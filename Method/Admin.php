<?php
namespace GDO\Comments\Method;

use GDO\Table\MethodQueryTable;
use GDO\Comments\GDO_Comment;
use GDO\UI\GDT_EditButton;

/**
 * @author gizmore
 * @version 7.0.0
 * @since 6.3.0
 */
final class Admin extends MethodQueryTable
{
	public function getPermission() : ?string { return 'staff'; }
	
    public function gdoTable()
    {
        return GDO_Comment::table();
    }

	public function gdoHeaders() : array
	{
		return array_merge(
			GDT_EditButton::make(),
			parent::gdoHeaders(),
		);
	}
	
	public function getQuery()
	{
		return GDO_Comment::table()->select()->order('comment_created DESC');
	}

}
