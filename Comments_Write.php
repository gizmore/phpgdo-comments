<?php
namespace GDO\Comments;

use GDO\Core\GDO;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\Date\Time;
use GDO\Mail\Mail;
use GDO\Core\GDT_Template;
use GDO\Core\GDT_Hook;
use GDO\Captcha\GDT_Captcha;

abstract class Comments_Write extends MethodForm
{
	/**
	 * @return GDO_CommentTable
	 */
	public abstract function gdoCommentsTable();

	public abstract function hrefList();

	protected GDO $object;
	
	protected GDO_Comment $oldComment;
	
	public function hasPermission(GDO_User $user) : bool
	{
		return $this->gdoCommentsTable()->canAddComment($user);
	}

	public function isApprovalRequired()
	{
		return Module_Comments::instance()->cfgApproval();
	}
	
	public function isCaptchaRequired()
	{
		return Module_Comments::instance()->cfgCaptcha();
	}
	
	public function createForm(GDT_Form $form) : void
	{
		$gdo = GDO_Comment::table();
// 		$form->addField($gdo->gdoColumn('comment_title'));
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
		
		if (1 === $this->gdoCommentsTable()->gdoMaxComments(GDO_User::current()))
		{
			$form->withGDOValuesFrom($this->oldComment);
		}
	}
	
	public function onInit()
	{
		$this->object = $this->gdoCommentsTable()->gdoCommentedObjectTable()->find(Common::getRequestString('id'));
		if (1 === $this->gdoCommentsTable()->gdoMaxComments(GDO_User::current()))
		{
			$this->oldComment = $this->object->getUserComment();
		}
	}
	
	public function execute()
	{
		$response = parent::execute();
		return $this->object->responseCard()->addField($response);
	}
	
	public function successMessage()
	{
	    return Module_Comments::instance()->cfgApproval() ? 
	    $this->redirectMessage('msg_comment_added_approval', null, $this->hrefList()) :
	    $this->redirectMessage('msg_comment_added', null, $this->hrefList());
	}
	
	public function formValidated(GDT_Form $form)
	{
		if ($this->oldComment)
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
				$comment->setVars(array(
					'comment_approved' => Time::getDate(),
					'comment_approver' => GDO_User::system()->getID(),
				));
			}
			$comment->insert();
			
			# Relation entry
			$entry = $this->gdoCommentsTable()->blank(array(
				'comment_object' => $this->object->getID(),
				'comment_id' => $comment->getID(),
			));
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
		
		$this->successMessage();
	}
	
	private function sendEmail(GDO_Comment $comment)
	{
		foreach (GDO_User::staff() as $user)
		{
			$this->sendEmailTo($user, $comment);
		}
	}
	
	private function sendEmailTo(GDO_User $user, GDO_Comment $comment)
	{
		$mail = new Mail();
		$mail->setSubject(tusr($user, 'mail_new_comment_title', [sitename()]));
		$tVars = array(
			'user' => $user,
			'comment' => $comment,
			'href_approve' => $comment->urlApprove(),
			'href_delete' => $comment->urlDelete(),
		);
		$mail->setBody(GDT_Template::phpUser($user, 'Comments', 'mail/new_comment.php', $tVars));
		$mail->setSender(GDO_BOT_EMAIL);
		$mail->sendToUser($user);
	}
}
