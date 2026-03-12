<?php

use MediaWiki\Extension\UnifiedTaskOverview\TaskStore;
use MediaWiki\MediaWikiServices;

return [
	'UnifiedTaskOverview.TaskStore' => static function ( MediaWikiServices $services ): TaskStore {
		return new TaskStore( $services->getConnectionProvider() );
	},
];
