<?php
namespace GDO\Comments\tpl;

use GDO\Comments\GDO_Comment;
use GDO\UI\GDT_Card;
use GDO\UI\GDT_EditButton;
use GDO\UI\GDT_HTML;
use GDO\User\GDO_User;

/** @var $gdo GDO_Comment * */

$user = GDO_User::current();

$card = GDT_Card::make()->gdo($gdo);
$card->creatorHeader();
$card->addField(
	GDT_HTML::make('comment_message')->var($gdo->displayMessage()),
);

if ($gdo->hasFile())
{
	$card->addField(
		$gdo->gdoColumn('comment_file'),
	);
}

if ($gdo->canEdit($user))
{
    $card->actions()->addFields(
        GDT_EditButton::make()->href($gdo->hrefEdit())->writeable($gdo->canEdit($user)),
    );
}


echo $card->render();
