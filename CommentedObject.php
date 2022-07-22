<?php
namespace GDO\Comments;

use GDO\DB\Query;
use GDO\User\GDO_User;

/**
 * This trait adds utilities for a commented object.
 * To make an object commented, follow these steps:
 * 
 * 1. Add a new DBTable/GDO extending CommentsTable
 *	This table has to return the commented object table in gdoCommentObjectTable() – e.g. GDO_News::table()
 *	
 * 2. Add this trait to your commented object.
 *	The commented object has to return your new DBTable in gdoCommentTable() – e.g. GDO_NewsComments::table()
 *
 * Your object is than able to easily add comments to the Comment table, joined via your new CommentsTable table.
 * All relations have foreign keys, as usual.
 *	 
 * @author gizmore
 * @version 7.0.0
 * @since 5.0.0
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
    public function gdoCommentHrefEdit() { return href('Comments', 'Edit'); }
	##########################################

    
    /**
	 * Get the number of comments
	 * @return number
	 */
	public function getCommentCount($withDeleted=false, $approvedOnly=true)
	{
		return $this->queryCountComments($withDeleted, $approvedOnly);
	}
	
	/**
	 * Query the number of comments.
	 * @return int
	 */
	public function queryCountComments($withDeleted=false, $approvedOnly=true)
	{
		$commentTable = $this->gdoCommentTable();
		$commentTable instanceof GDO_CommentTable;
		$query = $commentTable->select('COUNT(*)')->joinObject('comment_id');
		$query->where("comment_object={$this->getID()}");
		if (!$withDeleted)
		{
			$query->where("comment_deleted IS NULL");
		}
		if ($approvedOnly)
		{
			$query->where("comment_approved IS NOT NULL");
		}
		return $query->exec()->fetchValue();
	}
	
	/**
	 * Build query for all comments.
	 * @return Query
	 */
	public function queryComments($withDeleted=false, $approvedOnly=true)
	{
		$commentTable = $this->gdoCommentTable();
		$commentTable instanceof GDO_CommentTable;
		$query = $commentTable->select('gdo_comment.*')->
			fetchTable(GDO_Comment::table())->
			joinObject('comment_id')->
			where("comment_object=".$this->getID());

		if (!$withDeleted)
		{
			$query->where("comment_deleted IS NULL");
		}
		if ($approvedOnly)
		{
			$query->where("comment_approved IS NOT NULL");
		}
		
		return $query;
	}
	
	/**
	 * Build query for a single comment for a given user.
	 * @param GDO_User $user
	 * @return Query
	 */
	public function queryUserComments(GDO_User $user=null)
	{
		$user = $user ? $user : GDO_User::current();
		return $this->queryComments()->where("comment_creator={$user->getID()}");
	}
	
	/**
	 * In case you only allow one comment per user and object, this gets the comment for a user and object
	 * @param GDO_User $user
	 * @return GDO_Comment
	 */
	public function getUserComment(GDO_User $user=null)
	{
		return $this->queryUserComments($user)->first()->exec()->fetchObject();
	}
	

}
