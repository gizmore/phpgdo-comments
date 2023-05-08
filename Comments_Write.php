<?php
declare(strict_types=1);
namespace GDO\Comments;

use GDO\Captcha\GDT_Captcha;
use GDO\Core\GDO;
use GDO\Core\GDT;
use GDO\Core\GDT_Hook;
use GDO\Core\GDT_Object;
use GDO\Core\GDT_Template;
use GDO\Core\GDT_Tuple;
use GDO\Date\Time;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\Mail\Mail;
use GDO\UI\GDT_HTML;
use GDO\User\GDO_User;

/**
 * Abstract comment writing. @TODO: Rename to MethodCommentWrite
 *
 * @version 7.0.3
 * @since 6.3.0
 * @author gizmore
 */
abstract class Comments_Write extends MethodForm
{

	protected GDO $object;
	protected ?GDO_Comment $oldComment;

	public function isTrivial(): bool
	{
		return false;
	}

	public function hasPermission(GDO_User $user, string &$error, array &$args): bool
	{
		return $this->gdoCommentsTable()->canAddComment($user);
	}

	abstract public function gdoCommentsTable(): GDO_CommentTable;

	public function gdoParameters(): array
	{
		return [
			GDT_Object::make('id')->table($this->gdoCommentsTable()->gdoCommentedObjectTable())->notNull(),
		];
	}

	protected function createForm(GDT_Form $form): void
	{
		$gdo = GDO_Comment::table();
		$form->addField($gdo->gdoColumn('comment_message'));
		if ($this->gdoCommentsTable()->gdoAllowFiles())
		{
			$form->addField($gdo->gdoColumn('comment_file'));
		}

		if ($this->isCaptchaRequired())
		{
			if (module_enabled('Captcha'))
			{
				$form->addField(GDT_Captcha::make());
			}
		}
		$form->addFields(
			GDT_AntiCSRF::make(),
		);
		$form->actions()->addField(GDT_Submit::make());
	}

	public function getMethodTitle(): string
	{
		return t('btn_write_comment');
	}


	public function isCaptchaRequired(): string
	{
		return Module_Comments::instance()->cfgCaptcha();
	}

	public function onMethodInit(): ?GDT
	{
		$this->object = $this->gdoParameterValue('id');
		if ($this->gdoCommentsTable()->gdoMaxComments(GDO_User::current()) <= 1)
		{
			$this->oldComment = $this->object->getUserComment();
		}
		return null;
	}

	public function execute(): GDT
	{
		$card = $this->object->renderCard();
		$card = GDT_HTML::make()->var($card);
		$result = parent::execute();
		$response = GDT_Tuple::makeWith($card);
		if ($result)
		{
			$response->addField($result);
		}
		return $response;
	}

	public function formValidated(GDT_Form $form): GDT
	{
		if (isset($this->oldComment))
		{
			$this->oldComment->saveVars($form->getFormVars());
		}
		else
		{
			# Insert comment
			$comment = GDO_Comment::blank($form->getFormVars());
			$approval = Module_Comments::instance()->cfgApproval();
			if (!$approval)
			{
				$comment->setVars([
					'comment_approved' => Time::getDate(),
					'comment_approver' => GDO_User::system()->getID(),
				]);
			}
			$comment->insert();

			# Relation entry
			$entry = $this->gdoCommentsTable()->blank([
				'comment_object' => $this->object->getID(),
				'comment_id' => $comment->getID(),
			]);
			$entry->insert();

			if (Module_Comments::instance()->cfgEmail() || $approval)
			{
				$this->sendEmail($comment);
			}

			GDT_Hook::callWithIPC('CommentAdded', $comment);
			if (!$approval)
			{
				GDT_Hook::callWithIPC('CommentApproved', $comment);
			}
		}

		return $this->successMessage();
	}

	private function sendEmail(GDO_Comment $comment): void
	{
		foreach (GDO_User::staff() as $user)
		{
			$this->sendEmailTo($user, $comment);
		}
	}

	private function sendEmailTo(GDO_User $user, GDO_Comment $comment): void
	{
		$mail = Mail::botMail();
		$mail->setSubject(tusr($user, 'mail_new_comment_title', [sitename()]));
		$tVars = [
			'user' => $user,
			'comment' => $comment,
			'href_approve' => $comment->urlApprove(),
			'href_delete' => $comment->urlDelete(),
		];
		$mail->setBody(GDT_Template::phpUser($user, 'Comments', 'mail/new_comment.php', $tVars));
		$mail->sendToUser($user);
	}

	public function successMessage(): GDT
	{
		return Module_Comments::instance()->cfgApproval() ?
			$this->redirectMessage('msg_comment_added_approval', null, $this->hrefList()) :
			$this->redirectMessage('msg_comment_added', null, $this->hrefList());
	}

	##############
	### E-Mail ###
	##############

	abstract public function hrefList(): string;

	public function isApprovalRequired()
	{
		return Module_Comments::instance()->cfgApproval();
	}

}
