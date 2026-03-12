<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\HookHandler\LoadExtensionSchemaUpdates;

use MediaWiki\Installer\DatabaseUpdater;
use MediaWiki\Installer\Hook\LoadExtensionSchemaUpdatesHook;

class RunDatabaseUpdates implements LoadExtensionSchemaUpdatesHook {

	/**
	 * @param DatabaseUpdater $updater
	 *
	 * @return bool|void
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		$dbType = $updater->getDB()->getType();
		$dir = dirname( __DIR__, 3 );

		$updater->addExtensionTable(
			'uto_usertasks',
			"$dir/db/$dbType/uto_usertasks.sql"
		);
	}
}
