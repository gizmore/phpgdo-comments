<?php
declare(strict_types=1);
namespace GDO\Comments;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\User\GDO_User;

/**
 * Abstract comments.
 * Reused in news, forum, helpdesk etc.
 *
 * @version 7.0.3
 * @since 5.0.0
 * @author gizmore
 */
final class Module_Comments extends GDO_Module
{

	##############
	### Module ###
	##############
	public int $priority = 80;

	public function onLoadLanguage(): void { $this->loadLanguage('lang/comments'); }

	public function href_administrate_module(): ?string { return href('Comments', 'Admin'); }

	public function getClasses(): array
	{
		return [
			GDO_Comment::class,
			GDO_CommentLike::class,
		];
	}

	public function getDependencies(): array
	{
		return ['Votes', 'File'];
	}

	public function getFriendencies(): array
	{
		return ['Mail'];
	}

	##############
	### Config ###
	##############
	public function getConfig(): array
	{
		return [
			GDT_Checkbox::make('comment_email')->initial('1'),
			GDT_Checkbox::make('comment_approval_guest')->initial('1'),
			GDT_Checkbox::make('comment_approval_member')->initial('0'),
			GDT_Checkbox::make('comment_captcha_guest')->initial('1'),
			GDT_Checkbox::make('comment_captcha_member')->initial('0'),
		];
	}

	public function cfgEmail(): string { return $this->getConfigVar('comment_email'); }

	public function cfgCaptcha(): string
	{
		return GDO_User::current()->isMember() ?
			$this->cfgCaptchaMember() :
			$this->cfgCaptchaGuest();
	}

	public function cfgCaptchaMember(): string { return $this->getConfigVar('comment_captcha_member'); }

	public function cfgCaptchaGuest(): string { return $this->getConfigVar('comment_captcha_guest'); }

	public function cfgApproval(): string
	{
		return GDO_User::current()->isMember() ?
			$this->cfgApprovalMember() :
			$this->cfgApprovalGuest();
	}

	public function cfgApprovalMember(): string { return $this->getConfigVar('comment_approval_member'); }

	public function cfgApprovalGuest(): string { return $this->getConfigVar('comment_approval_guest'); }

}
