<?php
namespace GDO\Comments\Method;

use GDO\Core\GDT;
use GDO\Core\Method;
use GDO\File\GDT_File;
use GDO\File\Method\GetFile;

/**
 * Comment attachment download.
 *
 * @version 7.0.1
 * @since 6.5.0
 * @author gizmore
 */
final class Attachment extends Method
{

	public function gdoParameters(): array
	{
		return [
			GDT_File::make('id')->notNull(),
		];
	}

	public function getMethodTitle(): string
	{
		return t('attachment');
	}

	public function execute(): GDT
	{
		$id = $this->gdoParameterVar('id');
		return GetFile::make()->executeWithId($id);
	}

}
