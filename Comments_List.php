<?php
namespace GDO\Comments;

use GDO\Core\GDO;
use GDO\Table\MethodQueryCards;
use GDO\Table\GDT_List;
use GDO\Session\GDO_Session;
use GDO\Table\GDT_Table;
use GDO\Core\Website;
use GDO\DB\Query;
use GDO\UI\GDT_HTML;
use GDO\Core\GDT_Tuple;
use GDO\Core\GDT_Object;

/**
 * Abstract list of comments.
 *
 * @author gizmore
 * @version 7.0.1
 * @since 6.3.0
 */
abstract class Comments_List extends MethodQueryCards
{
	const LAST_LIST_KEY = 'comments_list_last';

	public function isTrivial(): bool
	{
		return false;
	}

	public function setLastList()
	{
		GDO_Session::set(self::LAST_LIST_KEY, urldecode($_SERVER['REQUEST_URI']));
	}

	public function getLastList()
	{
		return GDO_Session::set(self::LAST_LIST_KEY);
	}

	public function setupTitle(GDT_Table $table)
	{
		$gdoName = $this->gdoCommentsTable()->gdoHumanName();
		Website::setTitle(t('mt_list_comments', [
			$gdoName
		]));
		$table->title('list_comments', [
			$table->countItems
		]);
	}

	public function isShownInSitemap(): bool
	{
		return false;
	}

	/**
	 *
	 * @return GDO_CommentTable
	 */
	public abstract function gdoCommentsTable();

	public function gdoTable(): GDO
	{
		return $this->gdoCommentsTable();
	}

	public abstract function hrefAdd();

	protected ?GDO $object;
	
	public function gdoParameters() : array
	{
		$table = $this->gdoCommentsTable()->gdoCommentedObjectTable();
		return [
			GDT_Object::make()->table($table)->notNull(),
		];
	}

	public function onMethodInit()
	{
		parent::onMethodInit();
		$this->object = $this->gdoParameterValue('id');
	}

	public function getQuery(): Query
	{
		$query = $this->gdoTable()
			->select('comment_id_t.*')
			->joinObject('comment_id')
			->where("comment_deleted is NULL")
			->where("comment_object=" . $this->object->getID());
		$query->where("comment_approved IS NOT NULL");
		return $query->fetchTable(GDO_Comment::table());
	}

	public function execute()
	{
		$card = GDT_HTML::make()->var($this->object->renderCard());
		$resp = parent::execute();
		return GDT_Tuple::makeWith($card, $resp);
	}

	public function gdoDecorateList(GDT_List $list)
	{
		$count = $this->object->getCommentCount();
		$list->title('list_comments', [
			$this->object->displayName(),
			$count
		]);
	}

}
