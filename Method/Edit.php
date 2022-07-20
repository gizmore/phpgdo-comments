<?php
namespace GDO\Comments\Method;

use GDO\Comments\GDO_Comment;
use GDO\Core\GDO_Error;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\User\GDO_User;
use GDO\Util\Common;
use GDO\Core\GDT_String;
use GDO\Date\Time;
use GDO\Core\Website;
use GDO\UI\GDT_Redirect;
use GDO\Core\GDT_Response;
use GDO\UI\GDT_DeleteButton;

/**
 * Edit a comment.
 * 
 * @author gizmore
 * @version 7.0.0
 * @see Comments_List
 * @see Comments_Write
 * @see GDT_Message
 * @see GDT_File
 */
class Edit extends MethodForm
{
    public function showInSitemap() { return false; }
    
	public function gdoParameters() : array
	{
		return [
			GDT_String::make('id')->notNull(),
		];
	}
	
	public function execute()
	{
	    if (isset($_REQUEST['delete']))
	    {
	        if ($this->comment->canEdit(GDO_User::current()))
	        {
	            $this->comment->delete();
    	        return
        	        $this->message('msg_crud_deleted', [$this->comment->gdoHumanName()])->
        	        addField($this->redirectToList());
	        }
	    }
	    return parent::execute();
	}
	
	private function redirectToList()
	{
	    
	}
	
	protected GDO_Comment $comment;
	
	public function onInit() : void
	{
		$user = GDO_User::current();
		$this->comment = GDO_Comment::table()->find(Common::getRequestString('id'));
		if ($this->comment->isDeleted())
		{
		    throw new GDO_Error('err_is_deleted');
		}
		if (!$this->comment->canEdit($user))
		{
			throw new GDO_Error('err_no_permission');
		}
	}
	
	public function afterExecute() : void
	{
	    if (!$this->comment->isDeleted())
	    {
	        return GDT_Response::makeWithHTML($this->comment->renderCard());
	    }
	    else
	    {
	        return Website::redirectBack(6);
	    }
	}
	
	public function createForm(GDT_Form $form) : void
	{
		$form->addFields([
// 			$this->comment->gdoColumn('comment_title'),
			$this->comment->gdoColumn('comment_message'),
			$this->comment->gdoColumn('comment_file'),
			$this->comment->gdoColumn('comment_top'),
			GDT_AntiCSRF::make(),
		]);
		$form->actions()->addFields([
			GDT_Submit::make(),
			GDT_DeleteButton::make(),
		]);
		
		if (!$this->comment->isApproved())
		{
			$form->actions()->addField(GDT_Submit::make('approve'));
		}
		
		$form->withGDOValuesFrom($this->comment);
	}
	
	public function formValidated(GDT_Form $form)
	{
		$this->comment->saveVars($form->getFormVars());
		return $this->message('msg_comment_edited')->addField($this->renderPage());
	}
	
	public function onSubmit_delete(GDT_Form $form)
	{
		if ($file = $this->comment->getFile())
		{
			$file->delete();
		}
		$this->comment->markDeleted();
		return $this->message('msg_comment_deleted')->addField(GDT_Response::makeWith(GDT_Redirect::make()->href($this->hrefBack())));
	}
	
	public function hrefBack()
	{
		return Website::hrefBack();
	}
	
	public function onSubmit_approve(GDT_Form $form)
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
		
		Website::redirect(href('Comment', 'Admin', 12));
		return $this->message('msg_comment_approved')->addField($this->renderPage());
	}
}
