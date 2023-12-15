<?php
namespace GDO\Comments;

use GDO\Core\GDO;
use GDO\Core\GDT_AutoInc;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_CreatedBy;
use GDO\Core\GDT_DeletedAt;
use GDO\Core\GDT_DeletedBy;
use GDO\Core\GDT_EditedAt;
use GDO\Core\GDT_EditedBy;
use GDO\Core\GDT_Template;
use GDO\Date\GDT_DateTime;
use GDO\File\GDO_File;
use GDO\File\GDT_File;
use GDO\UI\GDT_Message;
use GDO\User\GDO_User;
use GDO\User\GDT_User;
use GDO\Votes\GDT_LikeCount;
use GDO\Votes\WithLikes;

/**
 * A comment.
 * Comments can be attached to objects by use CommentedObject.
 * Comments can be liked.
 *
 * @version 7.0.1
 * @since 6.0.0
 * @see CommentedObject
 *
 * @author gizmore@wechall.net
 */
final class GDO_Comment extends GDO
{

	use WithLikes;

	public function gdoLikeTable() { return GDO_CommentLike::table(); }

	public function gdoColumns(): array
	{
		return [
			GDT_AutoInc::make('comment_id'),
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
		];
	}

	public function getFile(): ?GDO_File { return $this->gdoValue('comment_file'); }

	public function hasFile(): bool { return $this->getFileID() !== null; }

	public function getFileID(): ?string { return $this->gdoVar('comment_file'); }

	public function getCreator(): GDO_User { return $this->gdoValue('comment_creator'); }

	public function getCreatorID(): string { return $this->gdoVar('comment_creator'); }

	public function getCreateDate(): string { return $this->gdoVar('comment_created'); }

	public function displayInput(): string { return $this->gdoVar('comment_message_input'); }

// 	public function getMessage() { return $this->gdoVar('comment_message_output');  }

	public function displayMessage(): string { return $this->gdoVar('comment_message_output'); }

	public function isApproved(): bool { return $this->gdoVar('comment_approved') !== null; }

// 	public function displayMessage() { return $this->gdoColumn('comment_message')->renderHTML();  }

	public function canEdit(GDO_User $user): bool
	{
		return $user->hasPermission('staff');
	}

	public function canDelete(GDO_User $user): bool
	{
		return $user->hasPermission('staff');
	}

	public function href_edit(): string
	{
		return $this->hrefEdit();
	}

	public function hrefEdit(): string
	{
		return href('Comments', 'Edit', '&comment=' . $this->getID());
	}

	public function getID(): ?string { return $this->gdoVar('comment_id'); }

	public function isDeleted(): bool { return $this->gdoVar('comment_deleted') !== null; }

	public function renderCard(): string
	{
		return GDT_Template::php('Comments', 'comment_card.php', ['gdo' => $this]);
	}

	public function urlApprove(): string
	{
		return url('Comments', 'Approve', '&id=' . $this->getID() . '&token=' . $this->gdoHashcode());
	}

	public function urlDelete()
	{
		return url('Comments', 'Delete', '&id=' . $this->getID() . '&token=' . $this->gdoHashcode());
	}

}
