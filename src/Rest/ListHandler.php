<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Rest;

use MediaWiki\Extension\UnifiedTaskOverview\TaskStore;
use MediaWiki\Language\Language;
use MediaWiki\Rest\SimpleHandler;

class ListHandler extends SimpleHandler {

	/**
	 * @param TaskStore $taskStore
	 * @param Language $language
	 */
	public function __construct(
		private readonly TaskStore $taskStore,
		private readonly Language $language
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function run() {
		$userIdentity = $this->getAuthority()->getUser();
		if ( !$userIdentity->isRegistered() ) {
			return $this->getResponseFactory()->createJson( [] );
		}

		$tasks = $this->taskStore->getTasksForUser( $userIdentity );

		$responseData = [];
		foreach ( $tasks as $task ) {
			$responseData[] = $task->serializeForOutput( $this->language );
		}

		$hookContainer = $this->getHookContainer();
		$hookContainer->run( 'UnifiedTaskOverviewTaskCollectionComplete', [ &$responseData ] );

		return $this->getResponseFactory()->createJson( $responseData );
	}

}
