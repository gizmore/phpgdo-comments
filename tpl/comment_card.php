<?php
use GDO\Comments\GDO_Comment;
use GDO\UI\GDT_EditButton;
use GDO\User\GDO_User;
use GDO\UI\GDT_Card;
use GDO\UI\GDT_HTML;

/** @var $gdo GDO_Comment **/
$user = GDO_User::current();

$card = GDT_Card::make()->gdo($gdo);
$card->creatorHeader();
$card->addFields(
	GDT_HTML::make('comment_message')->html($gdo->displayMessage()),
);

if ($gdo->hasFile())
{
	$card->addFields(
		$gdo->gdoColumn('comment_file'),
	);
}

$card->actions()->addFields(
	GDT_EditButton::make()->href($gdo->hrefEdit())->writeable($gdo->canEdit($user)),
);

echo $card->render();
