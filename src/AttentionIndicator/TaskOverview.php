<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\AttentionIndicator;

use BlueSpice\Discovery\AttentionIndicator;
use BlueSpice\Discovery\IAttentionIndicator;
use MediaWiki\Config\Config;
use MediaWiki\Extension\UnifiedTaskOverview\TaskStore;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

class TaskOverview extends AttentionIndicator {

	/**
	 * @param string $key
	 * @param Config $config
	 * @param User $user
	 * @param TaskStore $taskStore
	 */
	public function __construct( string $key, Config $config, User $user, private readonly TaskStore $taskStore ) {
		parent::__construct( $key, $config, $user );
	}

	/**
	 * @param string $key
	 * @param Config $config
	 * @param User $user
	 * @param MediaWikiServices $services
	 * @return IAttentionIndicator
	 */
	public static function factory( string $key, Config $config, User $user, MediaWikiServices $services ) {
		return new static(
			$key, $config, $user,
			$services->getService( 'UnifiedTaskOverview.TaskStore' ),
		);
	}

	protected function doIndicationCount(): int {
		if ( !$this->user || $this->user->isAnon() ) {
			return 0;
		}
		return $this->taskStore->countTasksForUser( $this->user );
	}
}
