<?php
namespace GDO\Comments\Method;

use GDO\Core\GDT_Object;
use GDO\Core\GDT_Template;
use GDO\Core\Method;
use GDO\Comments\GDO_Comment;
use GDO\Date\Time;
use GDO\User\GDO_User;
use GDO\Mail\Mail;
use GDO\Core\GDT_Hook;
use GDO\Core\GDT_Token;

/**
 * Comment approvement.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 6.5.0
 */
final class Approve extends Method
{
	public function getMethodTitle() : string
	{
		return t('mt_comments_admin');
	}
	
	public function gdoParameters() : array
	{
		return [
			GDT_Object::make('comment')->table(GDO_Comment::table())->notNull(),
			GDT_Token::make('token')->notNull(),
		];
	}
	
	public function getComment() : GDO_Comment
	{
		return $this->gdoParameterValue('id');
	}
	
	public function getToken() : string
	{
		return $this->gdoParameterVar('token');
	}
	
	public function execute()
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
