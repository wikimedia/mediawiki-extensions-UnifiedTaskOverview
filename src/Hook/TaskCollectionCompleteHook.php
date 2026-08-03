<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Hook;

interface TaskCollectionCompleteHook {

	/**
	 * @param array &$tasks
	 * @return void
	 */
	public function onUnifiedTaskOverviewTaskCollectionComplete( &$tasks );
}
