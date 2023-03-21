<?php
namespace GDO\Comments\Method;

use GDO\Admin\MethodAdmin;
use GDO\Comments\GDO_Comment;
use GDO\Core\GDO;
use GDO\DB\Query;
use GDO\Table\MethodQueryTable;
use GDO\UI\GDT_EditButton;

/**
 * @version 7.0.1
 * @since 6.3.0
 * @author gizmore
 */
final class Admin extends MethodQueryTable
{

	use MethodAdmin;

	public function getPermission(): ?string { return 'staff'; }

	public function getMethodTitle(): string
	{
		$n = $this->getTable()->getResult()->numRows();
		return t('list_comments', [$n]);
	}

	public function getTableTitle()
	{
		return $this->getMethodTitle();
	}

	public function gdoTable(): GDO
	{
		return GDO_Comment::table();
	}

	public function gdoHeaders(): array
	{
		return array_merge(
			[GDT_EditButton::make()],
			parent::gdoHeaders(),
		);
	}

	public function getQuery(): Query
	{
		return GDO_Comment::table()->select()->order('comment_created DESC');
	}

}
