<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Tests\Phpunit;

use MediaWiki\Extension\UnifiedTaskOverview\ITaskDescriptor;
use MediaWiki\Extension\UnifiedTaskOverview\TaskStore;
use MediaWiki\Language\RawMessage;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use MediaWiki\WikiMap\WikiMap;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Wikimedia\Rdbms\IConnectionProvider;
use Wikimedia\Rdbms\IDatabase;
use Wikimedia\Rdbms\InsertQueryBuilder;
use Wikimedia\Rdbms\SelectQueryBuilder;
use Wikimedia\Rdbms\UpdateQueryBuilder;

/**
 * @covers \MediaWiki\Extension\UnifiedTaskOverview\TaskStore
 */
class TaskStoreTest extends TestCase {

	/**
	 * @covers \MediaWiki\Extension\UnifiedTaskOverview\TaskStore::storeTask
	 */
	public function testStoreTaskInsertsWhenTaskDoesNotExist(): void {
		$user = $this->makeUser( 10, 'Alice' );
		$descriptor = $this->makeDescriptor();
		$taskId = $this->getTaskIdForDescriptor( $descriptor );

		$hasSelectBuilder = $this->makeSelectBuilderMock();
		$hasSelectBuilder->method( 'fetchField' )->willReturn( false );

		$insertBuilder = $this->createMock( InsertQueryBuilder::class );
		$insertBuilder->method( 'insert' )->with( 'uto_tasks' )->willReturnSelf();
		$insertBuilder->expects( $this->once() )->method( 'row' )
			->with( $this->callback( static function ( array $row ) use ( $taskId ) {
				return $row['uto_task_id'] === $taskId
					&& $row['uto_user_id'] === 10
					&& $row['uto_wiki_id'] === WikiMap::getCurrentWikiId()
					&& $row['uto_type'] === 'task-type'
					&& $row['uto_page_namespace'] === NS_MAIN
					&& $row['uto_page_title'] === 'Task_Page'
					&& $row['uto_since'] === '20260821101010'
					&& is_string( $row['uto_data'] );
			} ) )->willReturnSelf();
		$insertBuilder->method( 'caller' )->willReturnSelf();
		$insertBuilder->expects( $this->once() )->method( 'execute' );

		$primaryDb = $this->createMock( IDatabase::class );
		$primaryDb->method( 'timestamp' )->willReturn( '20260821101010' );
		$primaryDb->method( 'newSelectQueryBuilder' )->willReturn( $hasSelectBuilder );
		$primaryDb->method( 'newInsertQueryBuilder' )->willReturn( $insertBuilder );

		$provider = $this->createMock( IConnectionProvider::class );
		$provider->method( 'getPrimaryDatabase' )->willReturn( $primaryDb );

		$logger = $this->createMock( LoggerInterface::class );
		$logger->expects( $this->never() )->method( 'error' );

		$store = new TaskStore( $provider, $logger );
		$store->storeTask( $descriptor, $user );
	}

	/**
	 * @covers \MediaWiki\Extension\UnifiedTaskOverview\TaskStore::storeTask
	 */
	public function testStoreTaskUpdatesWhenTaskExists(): void {
		$user = $this->makeUser( 10, 'Alice' );
		$descriptor = $this->makeDescriptor();
		$taskId = $this->getTaskIdForDescriptor( $descriptor );

		$hasSelectBuilder = $this->makeSelectBuilderMock();
		$hasSelectBuilder->method( 'fetchField' )->willReturn( $taskId );

		$updateBuilder = $this->createMock( UpdateQueryBuilder::class );
		$updateBuilder->method( 'table' )->with( 'uto_tasks' )->willReturnSelf();
		$updateBuilder->expects( $this->once() )->method( 'set' )
			->with( $this->callback( static function ( array $row ) {
				return $row['uto_type'] === 'task-type'
					&& $row['uto_page_namespace'] === NS_MAIN
					&& $row['uto_page_title'] === 'Task_Page'
					&& is_string( $row['uto_data'] );
			} ) )->willReturnSelf();
		$updateBuilder->expects( $this->once() )->method( 'where' )->with( [
			'uto_user_id' => 10,
			'uto_task_id' => $taskId,
		] )->willReturnSelf();
		$updateBuilder->method( 'caller' )->willReturnSelf();
		$updateBuilder->expects( $this->once() )->method( 'execute' );

		$primaryDb = $this->createMock( IDatabase::class );
		$primaryDb->method( 'newSelectQueryBuilder' )->willReturn( $hasSelectBuilder );
		$primaryDb->method( 'newUpdateQueryBuilder' )->willReturn( $updateBuilder );

		$provider = $this->createMock( IConnectionProvider::class );
		$provider->method( 'getPrimaryDatabase' )->willReturn( $primaryDb );

		$logger = $this->createMock( LoggerInterface::class );
		$logger->expects( $this->never() )->method( 'error' );

		$store = new TaskStore( $provider, $logger );
		$store->storeTask( $descriptor, $user );
	}

	/**
	 * @covers \MediaWiki\Extension\UnifiedTaskOverview\TaskStore::storeTask
	 */
	public function testStoreTaskLogsAndRethrowsOnFailure(): void {
		$user = $this->makeUser( 10, 'Alice' );
		$descriptor = $this->makeDescriptor();

		$primaryDb = $this->createMock( IDatabase::class );
		$primaryDb->method( 'newSelectQueryBuilder' )->willThrowException( new RuntimeException( 'DB failed' ) );

		$provider = $this->createMock( IConnectionProvider::class );
		$provider->method( 'getPrimaryDatabase' )->willReturn( $primaryDb );

		$logger = $this->createMock( LoggerInterface::class );
		$logger->expects( $this->once() )->method( 'error' )
			->with( $this->stringContains( 'Failed to store task' ) );

		$store = new TaskStore( $provider, $logger );

		$this->expectException( RuntimeException::class );
		$store->storeTask( $descriptor, $user );
	}

	/**
	 * @return SelectQueryBuilder
	 */
	private function makeSelectBuilderMock(): SelectQueryBuilder {
		$selectBuilder = $this->createMock( SelectQueryBuilder::class );
		$selectBuilder->method( 'table' )->with( 'uto_tasks' )->willReturnSelf();
		$selectBuilder->method( 'fields' )->willReturnSelf();
		$selectBuilder->method( 'where' )->willReturnSelf();
		$selectBuilder->method( 'caller' )->willReturnSelf();
		return $selectBuilder;
	}

	private function makeUser( int $id, string $name ): UserIdentity {
		$user = $this->createMock( UserIdentity::class );
		$user->method( 'getId' )->willReturn( $id );
		$user->method( 'getName' )->willReturn( $name );
		return $user;
	}

	private function makeDescriptor(): ITaskDescriptor {
		$title = $this->createMock( Title::class );
		$title->method( 'getInterwiki' )->willReturn( '' );
		$title->method( 'getNamespace' )->willReturn( NS_MAIN );
		$title->method( 'getDBkey' )->willReturn( 'Task_Page' );
		$title->method( 'getPrefixedText' )->willReturn( 'Task Page' );

		$descriptor = $this->createMock( ITaskDescriptor::class );
		$descriptor->method( 'getUniqueKey' )->willReturn( 'unique-task-key' );
		$descriptor->method( 'getType' )->willReturn( 'task-type' );
		$descriptor->method( 'getTitle' )->willReturn( $title );
		$descriptor->method( 'getURL' )->willReturn( '/wiki/Task_Page' );
		$descriptor->method( 'getHeader' )->willReturn( new RawMessage( 'Header' ) );
		$descriptor->method( 'getSubHeader' )->willReturn( new RawMessage( 'SubHeader' ) );
		$descriptor->method( 'getBody' )->willReturn( new RawMessage( 'Body' ) );
		$descriptor->method( 'getSortKey' )->willReturn( 42 );
		$descriptor->method( 'getRLModules' )->willReturn( [ 'ext.unifiedTaskOverview' ] );
		return $descriptor;
	}

	private function getTaskIdForDescriptor( ITaskDescriptor $descriptor ): string {
		return md5( implode( '|', [
			$descriptor->getUniqueKey(),
			$descriptor->getType(),
			WikiMap::getCurrentWikiId()
		] ) );
	}
}
