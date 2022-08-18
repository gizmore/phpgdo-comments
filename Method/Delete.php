<?php
namespace GDO\Comments\Method;

use GDO\Comments\GDO_Comment;
use GDO\Core\GDT_Template;
use GDO\Core\Method;
use GDO\Core\GDT_String;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\Mail\Mail;
use GDO\Core\GDT_Hook;

/**
 * Delete a comment.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 6.9.0
 */
final class Delete extends Method
{
	public function gdoParameters() : array
	{
		return [
			GDT_String::make('id')->notNull(),
		];
	}
	
	public function getMethodTitle() : string
	{
		return t('mt_comments_delete');
	}
	
	public function execute()
	{
		$id = $this->gdoParameterVar('id');
		$comment = GDO_Comment::table()->find($id);
		if ($comment->isDeleted())
		{
			return $this->error('err_comment_already_deleted');
		}
		if ($comment->gdoHashcode() !== Common::getRequestString('token'))
		{
			return $this->error('err_token');
		}
		
		$this->deleteComment($comment);

		return $this->message('msg_comment_deleted');
	}
	
	public function deleteComment(GDO_Comment $comment)
	{
	    $comment->markDeleted();
	    
	    $this->sendEmail($comment);
	    
	    GDT_Hook::callWithIPC('CommentDeleted', $comment);
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
		$mail->setSubject(tusr($user, 'mail_deleted_comment_title', [sitename()]));
		$tVars = [
			'user' => $user,
			'comment' => $comment,
		];
		$mail->setBody(GDT_Template::phpUser($user, 'Comments', 'mail/deleted_comment.php', $tVars));
		$mail->sendToUser($user);
	}
	
}
