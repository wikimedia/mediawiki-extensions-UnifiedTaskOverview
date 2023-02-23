<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\HookHandler\SkinTemplateNavigation;

use MediaWiki\Hook\SkinTemplateNavigation__UniversalHook;

class Setup implements SkinTemplateNavigation__UniversalHook {

	/**
	 * // phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName
	 * @inheritDoc
	 */
	public function onSkinTemplateNavigation__Universal( $sktemplate, &$links ): void {
		if ( !isset( $links['mytasks'] ) ) {
			return;
		}
		$links['mytasks'] = [
			"text" => $sktemplate->msg( 'unifiedtaskoverview-personal-url-my-tasks' )->text(),
			"href" => \SpecialPage::getTitleFor( 'UnifiedTaskOverview' )->getLocalURL(),
			"active" => false,
			'data' => [ 'attentionindicator' => 'taskoverview' ],
			'position' => 10,
		];
	}
}
