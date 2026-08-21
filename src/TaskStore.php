<?php

namespace MediaWiki\Extension\UnifiedTaskOverview;

use InvalidArgumentException;
use MediaWiki\Page\PageIdentity;
use MediaWiki\User\UserIdentity;
use MediaWiki\WikiMap\WikiMap;
use Psr\Log\LoggerInterface;
use Throwable;
use Wikimedia\Rdbms\IConnectionProvider;

final class TaskStore {

	private const TABLE_NAME = 'uto_tasks';

	/**
	 * @param IConnectionProvider $connectionProvider
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		private readonly IConnectionProvider $connectionProvider,
		private readonly LoggerInterface $logger
	) {
	}

	/**
	 * @param ITaskDescriptor $descriptor
	 * @param UserIdentity $forUser
	 * @return void
	 * @throws Throwable
	 */
	public function storeTask( ITaskDescriptor $descriptor, UserIdentity $forUser ): void {
		$taskId = $this->makeTaskId( $descriptor );
		try {
			if ( $this->has( $taskId, $forUser ) ) {
				$this->update( $descriptor, $taskId, $forUser );
			} else {
				$this->insert( $descriptor, $taskId, $forUser );
			}
		} catch ( Throwable $e ) {
			$this->logger->error( "Failed to store task $taskId for user {$forUser->getName()}: {$e->getMessage()}" );
			throw $e;
		}
	}

	/**
	 * @param UserIdentity $forUser
	 * @return array|StoredTask[]
	 */
	public function getTasksForUser( UserIdentity $forUser ): array {
		$rows = $this->connectionProvider->getReplicaDatabase()->newSelectQueryBuilder()
			->table( self::TABLE_NAME )
			->fields( [
				'uto_task_id',
				'uto_type',
				'uto_data',
				'uto_since',
				'uto_wiki_id',
				'uto_page_namespace',
				'uto_page_title'
			] )
			->where( [
				'uto_user_id' => $forUser->getId()
			] )
			->caller( __METHOD__ )
			->fetchResultSet();

		$res = [];
		foreach ( $rows as $row ) {
			$res[] = new StoredTask(
				id: $row->uto_task_id,
				type: $row->uto_type,
				pageNamespace:  (int)$row->uto_page_namespace,
				pageTitle: $row->uto_page_title,
				created: \DateTime::createFromFormat( 'YmdHis', $row->uto_since ),
				wikiId: $row->uto_wiki_id,
				data: unserialize( $row->uto_data )
			);
		}

		return $res;
	}

	/**
	 * @param ITaskDescriptor $descriptor
	 * @param UserIdentity $forUser
	 * @return void
	 * @throws Throwable
	 */
	public function deleteTask( ITaskDescriptor $descriptor, UserIdentity $forUser ): void {
		try {
			$id = $this->makeTaskId( $descriptor );
			$this->connectionProvider->getPrimaryDatabase()->newDeleteQueryBuilder()
				->table( self::TABLE_NAME )
				->where( [
					'uto_user_id' => $forUser->getId(),
					'uto_task_id' => $id
				] )
				->caller( __METHOD__ )
				->execute();
		} catch ( Throwable $e ) {
			$this->logger->error( "Failed to delete task $id for user {$forUser->getName()}: {$e->getMessage()}" );
			throw $e;
		}
	}

	/**
	 * @param PageIdentity $page
	 * @param string $type
	 * @return void
	 * @throws Throwable
	 */
	public function clearForPage( PageIdentity $page, string $type ): void {
		try {
			$db = $this->connectionProvider->getPrimaryDatabase();
			$db->newDeleteQueryBuilder()
				->table( self::TABLE_NAME )
				->where( [
					'uto_page_namespace' => $page->getNamespace(),
					'uto_page_title' => $page->getDBkey(),
					'uto_type' => $type,
					'uto_wiki_id' => WikiMap::getCurrentWikiId()
				] )
				->caller( __METHOD__ )
				->execute();
		} catch ( Throwable $e ) {
			$this->logger->error( "Failed to delete tasks for page {$page->getPrefixedText()}: {$e->getMessage()}" );
			throw $e;
		}
	}

	/**
	 * @param ITaskDescriptor $descriptor
	 * @return string
	 */
	private function makeTaskId( ITaskDescriptor $descriptor ): string {
		return md5( implode( '|', [
			$descriptor->getUniqueKey(),
			$descriptor->getType(),
			WikiMap::getCurrentWikiId()
		] ) );
	}

	/**
	 * @param ITaskDescriptor $descriptor
	 * @return string
	 */
	private function serializeTaskData( ITaskDescriptor $descriptor ): string {
		return serialize( [
			// TODO: Can be displaytitle
			'page_title' => $descriptor->getTitle()->getPrefixedText(),
			'header' => $descriptor->getHeader()->serialize(),
			'sub-header' => $descriptor->getSubHeader()->serialize(),
			'body' => $descriptor->getBody()->serialize(),
			'url' => $descriptor->getURL(),
			'sortkey' => $descriptor->getSortKey(),
			'rlModules' => $descriptor->getRLModules(),
		] );
	}

	/**
	 * @param string $id
	 * @param UserIdentity $forUser
	 * @return bool
	 */
	private function has( string $id, UserIdentity $forUser ): bool {
		$dbr = $this->connectionProvider->getPrimaryDatabase();
		$userId = $forUser->getId();

		return $dbr->newSelectQueryBuilder()
			->table( static::TABLE_NAME )
			->fields( [ 'uto_task_id' ] )
			->where( [
				'uto_user_id' => $userId,
				'uto_task_id' => $id
			] )
			->caller( __METHOD__ )
			->fetchField() !== false;
	}

	/**
	 * @param ITaskDescriptor $descriptor
	 * @param string $taskId
	 * @param UserIdentity $forUser
	 * @return void
	 */
	private function update( ITaskDescriptor $descriptor, string $taskId, UserIdentity $forUser ): void {
		$row = $this->getTaskRowData( $descriptor, $forUser );

		$this->connectionProvider->getPrimaryDatabase()->newUpdateQueryBuilder()
			->table( self::TABLE_NAME )
			->set( $row )
			->where( [
				'uto_user_id' => $forUser->getId(),
				'uto_task_id' => $taskId,
			] )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * @param ITaskDescriptor $descriptor
	 * @param string $taskId
	 * @param UserIdentity $forUser
	 * @return void
	 */
	private function insert( ITaskDescriptor $descriptor, string $taskId, UserIdentity $forUser ): void {
		$db = $this->connectionProvider->getPrimaryDatabase();

		$row = $this->getTaskRowData( $descriptor, $forUser );
		$row['uto_task_id'] = $taskId;
		$row['uto_wiki_id'] = WikiMap::getCurrentWikiId();
		$row['uto_user_id'] = $forUser->getId();
		$row['uto_since'] = $db->timestamp();

		$db->newInsertQueryBuilder()
			->insert( self::TABLE_NAME )
			->row( $row )
			->caller( __METHOD__ )
			->execute();
	}

	/**
	 * @param ITaskDescriptor $descriptor
	 * @param UserIdentity $forUser
	 * @return array
	 */
	private function getTaskRowData( ITaskDescriptor $descriptor, UserIdentity $forUser ): array {
		$title = $descriptor->getTitle();
		if ( $title->getInterwiki() ) {
			throw new InvalidArgumentException( "Do not add tasks for foreign wiki pages" );
		}
		return [
			'uto_page_namespace' => $title->getNamespace(),
			'uto_page_title' => $title->getDBkey(),
			'uto_type' => $descriptor->getType(),
			'uto_data' => $this->serializeTaskData( $descriptor ),
		];
	}

}
