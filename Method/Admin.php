<?php
namespace GDO\Comments\Method;

use GDO\Table\MethodQueryTable;
use GDO\Comments\GDO_Comment;
use GDO\Core\GDO;
use GDO\DB\Query;
use GDO\UI\GDT_EditButton;

/**
 * @author gizmore
 * @version 7.0.1
 * @since 6.3.0
 */
final class Admin extends MethodQueryTable
{
	public function getPermission() : ?string { return 'staff'; }
	
	public function getMethodTitle() : string
	{
		return t('perm_admin');
	}
	
    public function gdoTable() : GDO
    {
        return GDO_Comment::table();
    }

	public function gdoHeaders() : array
	{
		return array_merge(
			[GDT_EditButton::make()],
			parent::gdoHeaders(),
		);
	}
	
	public function getQuery() : Query
	{
		return GDO_Comment::table()->select()->order('comment_created DESC');
	}

}
