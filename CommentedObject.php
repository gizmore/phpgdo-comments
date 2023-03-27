<?php
declare(strict_types=1);
namespace GDO\Comments;

use GDO\DB\Query;
use GDO\User\GDO_User;

/**
 * This trait adds utilities for a commented object.
 * To make an object commented, follow these steps:
 *
 * 1. Add a new DBTable/GDO extending CommentsTable
 *    This table has to return the commented object table in gdoCommentObjectTable() – e.g. GDO_News::table()
 *
 * 2. Add this trait to your commented object.
 *    The commented object has to return your new DBTable in gdoCommentTable() – e.g. GDO_NewsComments::table()
 *
 * Your object is than able to easily add comments to the Comment table, joined via your new CommentsTable table.
 * All relations have foreign keys, as usual.
 *
 * @version 7.0.3
 * @since 5.0.0
 * @author gizmore
 * @see Module_Comments
 * @see CommentTable
 * @see Comment
 */
trait CommentedObject
{

	######################################
	### Additions needed in your object :(
//	 public function gdoCommentTable() { return LUP_RoomComments::table(); } # Really abstract
//	 public function gdoCommentsEnabled() { return true; } # default true would be ok
//	 public function gdoCanComment(GDO_User $user) { return true; } default true would be ok
	public function gdoCommentHrefEdit(): string { return href('Comments', 'Edit'); }
	##########################################


	/**
	 * Get the number of comments
	 */
	public function getCommentCount(bool $withDeleted = false, bool $approvedOnly = true): int
	{
		return $this->queryCountComments($withDeleted, $approvedOnly);
	}

	/**
	 * Query the number of comments.
	 *
	 * @return int
	 */
	public function queryCountComments(bool $withDeleted = false, bool $approvedOnly = true): int
	{
		$commentTable = $this->gdoCommentTable();
		$query = $commentTable->select('COUNT(*)')->joinObject('comment_id');
		$query->where("comment_object={$this->getID()}");
		if (!$withDeleted)
		{
			$query->where('comment_deleted IS NULL');
		}
		if ($approvedOnly)
		{
			$query->where('comment_approved IS NOT NULL');
		}
		return (int) $query->exec()->fetchValue();
	}

	/**
	 * In case you only allow one comment per user and object, this gets the comment for a user and object
	 */
	public function getUserComment(GDO_User $user = null): ?static
	{
		return $this->queryUserComments($user)->first()->exec()->fetchObject();
	}

	/**
	 * Build query for a single comment for a given user.
	 */
	public function queryUserComments(GDO_User $user = null): Query
	{
		$user = $user ? $user : GDO_User::current();
		return $this->queryComments()->where("comment_creator={$user->getID()}");
	}

	/**
	 * Build query for all comments.
	 *
	 * @return Query
	 */
	public function queryComments($withDeleted = false, $approvedOnly = true)
	{
		$commentTable = $this->gdoCommentTable();
		$query = $commentTable->select('comment_id_t.*')->
		fetchTable(GDO_Comment::table())->
		joinObject('comment_id')->
		where('comment_object=' . $this->getID());

		if (!$withDeleted)
		{
			$query->where('comment_deleted IS NULL');
		}
		if ($approvedOnly)
		{
			$query->where('comment_approved IS NOT NULL');
		}

		return $query;
	}


}
