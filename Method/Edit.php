<?php
namespace GDO\Comments\Method;

use GDO\Comments\GDO_Comment;
use GDO\Core\GDO_Error;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\User\GDO_User;
use GDO\Date\Time;
use GDO\UI\GDT_DeleteButton;
use GDO\Core\GDT_Object;

/**
 * Edit a comment.
 * 
 * @author gizmore
 * @version 7.0.2
 * @since 6.4.0
 * @see Comments_List
 * @see Comments_Write
 * @see GDT_Message
 * @see GDT_File
 */
class Edit extends MethodForm
{
	protected GDO_Comment $comment;
	
	public function isShownInSitemap() : bool { return false; }
    
	public function gdoParameters() : array
	{
		return [
			GDT_Object::make('comment')->notNull()->table(GDO_Comment::table()),
		];
	}
	
	public function getComment() : GDO_Comment
	{
		return $this->gdoParameterValue('comment');
	}
	
	public function onMethodInit()
	{
		$user = GDO_User::current();
		$this->comment = $this->getComment();
		if (!$this->comment->canEdit($user))
		{
			throw new GDO_Error('err_permission_required');
		}
	}

	public function createForm(GDT_Form $form) : void
	{
		$this->comment = $this->getComment();
		$form->addFields(
			$this->comment->gdoColumn('comment_message'),
			$this->comment->gdoColumn('comment_file'),
			$this->comment->gdoColumn('comment_top'),
			GDT_AntiCSRF::make(),
		);
		
		$isDeleted = $this->comment->isDeleted();
		$isApproved = $this->comment->isApproved();
		$form->actions()->addFields(
			GDT_Submit::make(),
			GDT_Submit::make('approve')->onclick([$this, 'onApprove'])->disabled($isApproved),
			GDT_DeleteButton::make()->onclick([$this, 'onDelete'])->disabled($isDeleted),
		);
	}
	
	public function formValidated(GDT_Form $form)
	{
		$this->comment->saveVars($form->getFormVars());
		return $this->message('msg_comment_edited')->addField($this->renderPage());
	}
	
	public function onDelete(GDT_Form $form)
	{
		$this->comment->markDeleted();
		return $this->redirectMessage('msg_comment_deleted');
	}

	public function onApprove(GDT_Form $form)
	{
		if ($this->comment->isApproved())
		{
			return $this->error('err_comment_already_approved');
		}
		
		$this->comment->saveVars([
			'comment_approved' => Time::getDate(),
			'comment_approvor' => GDO_User::current()->getID(),
		]);
		
		Approve::make()->sendEmail($this->comment);
		
		return $this->redirectMessage('msg_comment_approved');
	}
}
