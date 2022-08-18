<?php
namespace GDO\Comments\Method;

use GDO\Core\Method;
use GDO\Core\GDT_String;
use GDO\File\Method\GetFile;

/**
 * Comment attachment download.
 * 
 * @author gizmore
 * @version 7.0.0
 * @since 6.5.0
 */
final class Attachment extends Method
{
	public function gdoParameters() : array
	{
		return [
			GDT_String::make('id')->notNull(),
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
