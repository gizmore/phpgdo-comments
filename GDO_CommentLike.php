<?php
namespace GDO\Comments;

use GDO\Votes\GDO_LikeTable;

/**
 * It is possible to like comments.
 *
 * @since 5.0
 * @author gizmore
 * @see Module_Votes
 * @see GDO_LikeTable
 */
final class GDO_CommentLike extends GDO_LikeTable
{

	public function gdoLikeObjectTable() { return GDO_Comment::table(); }

}
