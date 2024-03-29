<?php
declare(strict_types=1);
namespace GDO\Comments\Method;

use GDO\Comments\GDO_Comment;
use GDO\Core\GDT;
use GDO\Core\GDT_Hook;
use GDO\Core\GDT_Object;
use GDO\Core\GDT_Template;
use GDO\Core\GDT_Token;
use GDO\Core\Method;
use GDO\Date\Time;
use GDO\Mail\Mail;
use GDO\User\GDO_User;

/**
 * Comment approvement.
 *
 * @version 7.0.3
 * @since 6.5.0
 * @author gizmore
 */
final class Approve extends Method
{

	public function isTrivial(): bool
	{
		return false;
	}

	public function getMethodTitle(): string
	{
		return t('mt_comments_admin');
	}

	public function gdoParameters(): array
	{
		return [
			GDT_Object::make('id')->table(GDO_Comment::table())->notNull(),
			GDT_Token::make('token')->notNull(),
		];
	}

	public function execute(): GDT
	{
		$comment = $this->getComment();
		if ($comment->isApproved())
		{
			return $this->error('err_comment_already_approved');
		}
		if ($comment->gdoHashcode() !== $this->getToken())
		{
			return $this->error('err_token');
		}
		$comment->saveVars([
			'comment_approved' => Time::getDate(),
			'comment_approvor' => GDO_User::current()->getID(),
		]);

		$this->sendEmail($comment);

		GDT_Hook::callWithIPC('CommentApproved', $comment);

		return $this->message('msg_comment_approved');
	}

	public function getComment(): GDO_Comment
	{
		return $this->gdoParameterValue('id');
	}

	public function getToken(): string
	{
		return $this->gdoParameterVar('token');
	}

	public function sendEmail(GDO_Comment $comment)
	{
		foreach (GDO_User::staff() as $user)
		{
			$this->sendEmailTo($user, $comment);
		}
	}

	private function sendEmailTo(GDO_User $user, GDO_Comment $comment)
	{
		$mail = Mail::botMail();
		$mail->setSubject(tusr($user, 'mail_approved_comment_title', [sitename()]));
		$tVars = [
			'user' => $user,
			'comment' => $comment,
		];
		$mail->setBody(GDT_Template::phpUser($user, 'Comments', 'mail/approved_comment.php', $tVars));
		$mail->sendToUser($user);
	}

}
