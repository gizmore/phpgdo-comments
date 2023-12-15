<?php
declare(strict_types=1);
namespace GDO\Comments;

use GDO\Core\GDO;
use GDO\Core\GDO_DBException;
use GDO\Core\GDO_Exception;
use GDO\Core\GDO_ExceptionFatal;
use GDO\Core\GDT_Object;
use GDO\User\GDO_User;

/**
 * An abstract comments table.
 * You need to override gdoCommentedObjectTable()
 *
 * @version 7.0.3
 * @since 6.1.0
 */
class GDO_CommentTable extends GDO
{

	public function isTestable(): bool
	{
		return !$this->gdoAbstract();
	}

	################
	### Comments ###
	################
    /**
     * @throws GDO_DBException
     * @throws GDO_ExceptionFatal
     */
    public static function getCommentedObjectByCommentS(GDO_Comment $comment, GDO $fetchAs = null): ?GDO
	{
		$fetchAs = $fetchAs === null ?
			self::table()->gdoCommentedObjectTable() :
			$fetchAs;
		return self::table()->select()->
		fetchTable($fetchAs)->
		joinObject('comment_object')->
		where("comment_id={$comment->getID()}")->
		first()->exec()->fetchObject();
	}

    /**
     * @throws GDO_DBException
     * @throws GDO_ExceptionFatal
     */
    public function getCommentedObjectByComment(GDO_Comment $comment, GDO $fetchAs = null): ?GDO
    {
        return self::getCommentedObjectByCommentS($comment, $fetchAs);
    }

	public function gdoCommentedObjectTable(): GDO
	{
		throw new GDO_ExceptionFatal('err_comment_object_not_given', [$this->gdoHumanName()]);
	}

	/**
	 * @param string $className
	 *
	 * @return GDO_CommentTable
	 */
	public static function getInstance(string $className): static
	{
		$table = GDO::tableFor($className);
		if (!($table instanceof GDO_CommentTable))
		{
			throw new GDO_Exception('err_comment_table', [html($className)]);
		}
		return $table;
	}

	public function gdoEnabled(): bool { return true; }

	public function gdoAllowTitle(): bool { return true; }

	public function gdoAllowFiles(): bool { return true; }

	public function gdoMaxComments(GDO_User $user): int { return 100; }

	public function canAddComment(GDO_User $user): bool { return true; }

	###########
	### GDO ###
	###########

	public function canEditComment(GDO_Comment $comment, GDO_User $user): bool { return $comment->canEdit($user); }

	public function canDeleteComment(GDO_Comment $comment, GDO_User $user): bool { return $comment->canDelete($user); }

	public function gdoAbstract(): bool
	{
		return get_class($this) === self::class;
	}

	public function gdoColumns(): array
	{
		return [
			GDT_Object::make('comment_id')->primary()->table(GDO_Comment::table())->cascade(),
			GDT_Object::make('comment_object')->primary()->table($this->gdoCommentedObjectTable())->cascade(),
		];
	}

	###

	/**
	 * @return GDO
	 */
	public function getCommentedObject(): GDO { return $this->gdoValue('comment_object'); }

}
