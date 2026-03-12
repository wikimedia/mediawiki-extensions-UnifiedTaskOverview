<?php

namespace MediaWiki\Extension\UnifiedTaskOverview;

use MediaWiki\User\User;
use Wikimedia\Rdbms\IConnectionProvider;

class TaskStore {

	private IConnectionProvider $connectionProvider;

	public function __construct( IConnectionProvider $connectionProvider ) {
		$this->connectionProvider = $connectionProvider;
	}

	public function updateTask(
		ITaskDescriptor $descriptor,
		User $user,
		bool $isCompleted
	): void {
		$dbw = $this->connectionProvider->getPrimaryDatabase();
		$pageId = $descriptor->getTitle()->getId();
		$userId = $user->getId();
		$type = $descriptor->getType();
		$key = $descriptor->getUniqueKey();
		$wikiId = defined( 'FARMER_CALLED_INSTANCE' ) ? FARMER_CALLED_INSTANCE : '';
		$pageTitle = $descriptor->getTitle()->getPrefixedDBkey();

		if ( $isCompleted ) {
			$dbw->newDeleteQueryBuilder()
				->deleteFrom( 'uto_usertasks' )
				->where( [
					'uto_user_id' => $userId,
					'uto_type' => $type,
					'uto_key' => $key,
					'uto_wiki_id' => $wikiId
				] )
				->caller( __METHOD__ )
				->execute();
			return;
		}

		$dbw->upsert(
			'uto_usertasks',
			[
				'uto_page_id' => $pageId,
				'uto_user_id' => $userId,
				'uto_type' => $type,
				'uto_key' => $key,
				'uto_wiki_id' => $wikiId,
				'uto_page_title' => $pageTitle
			],
			[
				[
					'uto_user_id',
					'uto_type',
					'uto_key',
					'uto_wiki_id'
				]
			],
			[
				'uto_page_id' => $pageId,
				'uto_page_title' => $pageTitle
			],
			__METHOD__
		);
	}

}
