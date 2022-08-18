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
use GDO\Core\Website;
use GDO\UI\GDT_Redirect;
use GDO\Core\GDT_Response;
use GDO\UI\GDT_DeleteButton;
use GDO\UI\GDT_Page;
use GDO\Core\GDT_Object;

/**
 * Edit a comment.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 6.4.0
 * @see Comments_List
 * @see Comments_Write
 * @see GDT_Message
 * @see GDT_File
 */
class Edit extends MethodForm
{
    public function isShownInSitemap() : bool { return false; }
    
	public function gdoParameters() : array
	{
		return [
			GDT_Object::make('id')->notNull()->table(GDO_Comment::table()),
		];
	}
	
	public function getComment() : GDO_Comment
	{
		return $this->gdoParameterValue('id');
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
	
	public function onInit()
	{
		$user = GDO_User::current();
		$this->comment = $this->getComment();
		if ($this->comment->isDeleted())
		{
		    throw new GDO_Error('err_is_deleted');
		}
		if (!$this->comment->canEdit($user))
		{
			throw new GDO_Error('err_no_permission');
		}
	}
	
	/**
	 * After execution we show the card again,
	 * unless the comment got deleted, then we redirect back.
	 */
	public function afterExecute() : void
	{
		$response = GDT_Page::$INSTANCE->topResponse();
	    if (!$this->comment->isDeleted())
	    {
	    	$response->addField($this->comment);
	    }
	    else
	    {
	        $response->addField($this->redirectBack());
	    }
	}
	
	public function createForm(GDT_Form $form) : void
	{
		$form->addFields(
			$this->comment->gdoColumn('comment_message'),
			$this->comment->gdoColumn('comment_file'),
			$this->comment->gdoColumn('comment_top'),
			GDT_AntiCSRF::make(),
		);
		$form->actions()->addFields(
			GDT_Submit::make(),
			GDT_DeleteButton::make()->onclick([$this, 'onDelete']),
		);
		if (!$this->comment->isApproved())
		{
			$form->actions()->addField(GDT_Submit::make('approve')->onclick([$this, 'onApprove']));
		}
		$form->withGDOValuesFrom($this->comment);
	}
	
	public function formValidated(GDT_Form $form)
	{
		$this->comment->saveVars($form->getFormVars());
		return $this->message('msg_comment_edited')->addField($this->renderPage());
	}
	
	public function onDelete(GDT_Form $form)
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
		
		$href = href('Comments', 'Admin', 12);
		return $this->redirectMessage('msg_comment_approved', $href)->addField($this->renderPage());
	}
}
