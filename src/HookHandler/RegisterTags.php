<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\HookHandler;

use MediaWiki\Extension\UnifiedTaskOverview\Tag\MyTasks;
use MWStake\MediaWiki\Component\GenericTagHandler\Hook\MWStakeGenericTagHandlerInitTagsHook;

class RegisterTags implements MWStakeGenericTagHandlerInitTagsHook {

	/**
	 * @inheritDoc
	 */
	public function onMWStakeGenericTagHandlerInitTags( array &$tags ) {
		$tags[] = new MyTasks();
	}
}
