<?php
namespace GDO\Comments;

use GDO\Core\GDO;
use GDO\File\GDO_File;
use GDO\File\GDT_File;
use GDO\Core\GDT_Template;
use GDO\UI\GDT_Message;
use GDO\User\GDO_User;
use GDO\Votes\GDT_LikeCount;
use GDO\Votes\WithLikes;
use GDO\Date\GDT_DateTime;
use GDO\User\GDT_User;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_CreatedBy;
use GDO\Core\GDT_EditedAt;
use GDO\Core\GDT_EditedBy;
use GDO\Core\GDT_DeletedAt;
use GDO\Core\GDT_DeletedBy;

/**
 * A comment.
 * Comments can be attached to objects by use CommentedObject.
 * Comments can be liked.
 * 
 * @see CommentedObject
 * 
 * @author gizmore@wechall.net
 * @version 7.0.0
 * @since 6.0.0
 */
final class GDO_Comment extends GDO
{
	use WithLikes;
	public function gdoLikeTable() { return GDO_CommentLike::table(); }
	
	public function gdoColumns() : array
	{
		return array(
			GDT_AutoInc::make('comment_id'),
// 			GDT_String::make('comment_title')->notNull(),
			GDT_Message::make('comment_message')->notNull(),
			GDT_File::make('comment_file'),
			GDT_LikeCount::make('comment_likes'),
			GDT_Checkbox::make('comment_top')->writeable(false)->initial('0'),
			GDT_CreatedAt::make('comment_created'),
			GDT_CreatedBy::make('comment_creator'),
			GDT_DateTime::make('comment_approved'),
			GDT_User::make('comment_approvor')->label('comment_approvor'),
			GDT_EditedAt::make('comment_edited'),
			GDT_EditedBy::make('comment_editor'),
			GDT_DeletedAt::make('comment_deleted'),
			GDT_DeletedBy::make('comment_deletor'),
		);		
	}
	
	public function getID() : ?string { return $this->gdoVar('comment_id'); }
	
	/**
	 * @return GDO_File
	 */
	public function getFile() { return $this->gdoValue('comment_file'); }
	public function hasFile() { return $this->getFileID() !== null; }
	public function getFileID() { return $this->gdoVar('comment_file'); }
	/**
	 * @return GDO_User
	 */
	public function getCreator() { return $this->gdoValue('comment_creator'); }
	public function getCreatorID() { return $this->gdoVar('comment_creator'); }
	public function getCreateDate() { return $this->gdoVar('comment_created'); }
	
// 	public function getTitle() { return $this->gdoVar('comment_title');  }
	public function getMessage() { return $this->gdoVar('comment_message');  }
	public function displayMessage() { return $this->gdoColumn('comment_message')->renderCell();  }
	
	public function isApproved() { return $this->gdoVar('comment_approved') !== null; }
	public function isDeleted() : bool { return $this->gdoVar('comment_deleted') !== null; }
	
	public function renderCard() : string
	{
		return GDT_Template::php('Comments', 'comment_card.php', ['gdo' => $this]);
	}
	
	public function canEdit(GDO_User $user)
	{
		return $user->hasPermission('staff');
	}
	
	public function canDelete(GDO_User $user)
	{
		return $user->hasPermission('staff');
	}
	
	public function hrefEdit()
	{
		return href('Comments', 'Edit', '&id='.$this->getID());
	}
	
	public function href_edit()
	{
		return $this->hrefEdit();
	}
	
	public function urlApprove()
	{
		return url('Comments', 'Approve', '&id='.$this->getID().'&token='.$this->gdoHashcode());
	}
	
	public function urlDelete()
	{
		return url('Comments', 'Delete', '&id='.$this->getID().'&token='.$this->gdoHashcode());
	}
	
}
