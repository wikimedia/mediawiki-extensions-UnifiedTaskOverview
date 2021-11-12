<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Hook;

use MediaWiki\Extension\UnifiedTaskOverview\ITaskDescriptor;
use User;

interface GetTaskDescriptors {

	/**
	 *
	 * @param ITaskDescriptor[] &$descriptors
	 * @param User $user
	 * @return void
	 */
	public function onUnifiedTaskOverviewGetTaskDescriptors( &$descriptors, User $user );
}
