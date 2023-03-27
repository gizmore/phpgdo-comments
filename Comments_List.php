<?php
namespace GDO\Comments;

use GDO\Core\GDO;
use GDO\Core\GDT;
use GDO\Core\GDT_Object;
use GDO\Core\GDT_Tuple;
use GDO\Core\Website;
use GDO\DB\Query;
use GDO\Session\GDO_Session;
use GDO\Table\GDT_List;
use GDO\Table\GDT_Table;
use GDO\Table\MethodQueryCards;
use GDO\UI\GDT_HTML;

/**
 * Abstract list of comments.
 *
 * @version 7.0.1
 * @since 6.3.0
 * @author gizmore
 */
abstract class Comments_List extends MethodQueryCards
{

	public const LAST_LIST_KEY = 'comments_list_last';
	protected ?GDO $object;

	public function isTrivial(): bool
	{
		return false;
	}

	public function setupTitle(GDT_Table $table): void
	{
		$gdoName = $this->gdoCommentsTable()->gdoHumanName();
		Website::setTitle(t('mt_list_comments', [
			$gdoName,
		]));
		$table->title('list_comments', [
			$table->countItems,
		]);
	}

	/**
	 *
	 * @return GDO_CommentTable
	 */
	abstract public function gdoCommentsTable();

	public function isShownInSitemap(): bool
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

	abstract public function hrefAdd();

	public function gdoDecorateList(GDT_List $list)
	{
		$count = $this->object->getCommentCount();
		$list->title('list_comments', [
			$this->object->renderName(),
			$count,
		]);
	}	public function gdoTable(): GDO
	{
		return $this->gdoCommentsTable();
	}




	public function gdoParameters(): array
	{
		$table = $this->gdoCommentsTable()->gdoCommentedObjectTable();
		return [
			GDT_Object::make('id')->table($table)->notNull(),
		];
	}

	public function onMethodInit(): ?GDT
	{
		$this->object = $this->gdoParameterValue('id');
		return null;
	}

	public function getQuery(): Query
	{
		$query = $this->gdoTable()
			->select('comment_id_t.*')
			->joinObject('comment_id')
			->where('comment_deleted is NULL')
			->where('comment_object=' . $this->object->getID());
		return $query->where('comment_approved IS NOT NULL')->fetchTable($this->gdoFetchAs());
	}

	public function gdoFetchAs(): GDO
	{
		return GDO_Comment::table();
	}

	public function execute(): GDT
	{
		$card = GDT_HTML::make()->var($this->object->renderCard());
		$resp = parent::execute();
		return GDT_Tuple::makeWith($card, $resp);
	}

}
