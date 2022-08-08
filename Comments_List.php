<?php
namespace GDO\Comments;

use GDO\Core\GDO;
use GDO\Table\MethodQueryCards;
use GDO\Util\Common;
use GDO\Table\GDT_List;
use GDO\Core\GDT_Response;
use GDO\Session\GDO_Session;
use GDO\Table\GDT_Table;
use GDO\Core\Website;

abstract class Comments_List extends MethodQueryCards
{
    const LAST_LIST_KEY = 'comments_list_last';
    
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
    	Website::setTitle(t('mt_list_comments', [$gdoName]));
    	$table->title('list_comments', [$table->countItems]);
    }
        
	public function isShownInSitemap() : bool { return false; }
	
	/**
	 * @return GDO_CommentTable
	 */
	public abstract function gdoCommentsTable();
	public function gdoTable() : GDO { return $this->gdoCommentsTable(); }
	
	public abstract function hrefAdd();
	
	/**
	 * @var GDO
	 */
	protected $object;
	
	public function onInit()
	{
		parent::onInit();
		$this->object = $this->gdoCommentsTable()->gdoCommentedObjectTable()->find(Common::getRequestString('id'));
	}
	
	public function getQuery()
	{
		$query = $this->gdoTable()->select('comment_id_t.*')->
		  where("comment_deleted is NULL")->
		  where("comment_object=".$this->object->getID());
		$query->where("comment_approved IS NOT NULL");
		return $query->fetchTable(GDO_Comment::table());
	}
	
	/**
	 * @return GDT_Response
	 */
	public function execute()
	{
		return $this->object->responseCard()->addField(parent::execute());
	}

	public function gdoDecorateList(GDT_List $list)
	{
		$count = $this->object->getCommentCount();
		$list->title(t('list_comments', [$this->object->displayName(), $count]));
	}

}
