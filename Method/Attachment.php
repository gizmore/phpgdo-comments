<?php
namespace GDO\Comments\Method;

use GDO\Core\Method;
use GDO\File\Method\GetFile;
use GDO\File\GDT_File;

/**
 * Comment attachment download.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 6.5.0
 */
final class Attachment extends Method
{
	public function gdoParameters() : array
	{
		return [
			GDT_File::make('id')->notNull(),
		];
	}
	
	public function getMethodTitle() : string
	{
		return t('attachment');
	}
	
	public function execute()
	{
		return GetFile::make()->execute();
	}

}
