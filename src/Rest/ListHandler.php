<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Rest;

use MediaWiki\Extension\UnifiedTaskOverview\ITaskDescriptor;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MediaWiki\Rest\SimpleHandler;
use MediaWiki\Title\Title;
use MediaWiki\User\UserFactory;
use MWStake\MediaWiki\Component\ManifestRegistry\ManifestAttributeBasedRegistry;
use Wikimedia\Rdbms\IConnectionProvider;

class ListHandler extends SimpleHandler {

	/** @var UserFactory */
	protected $userFactory;

	/** @var IConnectionProvider */
	protected $connectionProvider;

	/**
	 * @param UserFactory $userFactory
	 * @param IConnectionProvider $connectionProvider
	 */
	public function __construct(
		UserFactory $userFactory,
		IConnectionProvider $connectionProvider
	) {
		$this->userFactory = $userFactory;
		$this->connectionProvider = $connectionProvider;
	}

	/**
	 * @inheritDoc
	 */
	public function run() {
		$userIdentity = $this->getAuthority()->getUser();
		if ( !$userIdentity->isRegistered() ) {
			return $this->getResponseFactory()->createJson( [] );
		}

		$dbr = $this->connectionProvider->getReplicaDatabase();
		$user = $this->userFactory->newFromUserIdentity( $userIdentity );

		$res = $dbr->newSelectQueryBuilder()
			->select( [
				'uto_type',
				'uto_page_id',
				'uto_key',
				'uto_wiki_id',
				'uto_page_title'
			] )
			->from( 'uto_usertasks' )
			->where( [
				'uto_user_id' => $user->getId(),
			] )
			->caller( __METHOD__ )
			->fetchResultSet();

		/** @var ManifestAttributeBasedRegistry */
		$registry = MediaWikiServices::getInstance()->getService( 'MWStakeManifestRegistryFactory' )
			->get( 'UnifiedTaskOverviewTaskDescriptorRegistry' );

		$responseData = [];
		foreach ( $res as $row ) {
			$class = $this->resolveTaskDescriptorClass( $registry, $row->uto_type );
			if ( !$class ) {
				continue;
			}

			/** @var ITaskDescriptor */
			$taskDescriptor = $class::newFromTaskRow( $row );
			if ( $taskDescriptor ) {
				$responseData[] = [
					'type' => $taskDescriptor->getType(),
					'header' => $taskDescriptor->getHeader()->parse(),
					'subheader' => $taskDescriptor->getSubHeader()->text(),
					'body' => $taskDescriptor->getBody()->parse(),
					'url' => $taskDescriptor->getURL(),
					'sortkey' => $taskDescriptor->getSortKey(),
					'RLmodules' => $taskDescriptor->getRLModules(),
					'wiki_id' => $row->uto_wiki_id,
					'page_title' => $row->uto_page_title,
					'page_name' => $this->getPageName( $row->uto_page_title ),
					'namespace' => $this->getNamespaceText( $row->uto_page_title )
				];
			} else {
				$title = Title::newFromDBkey( $row->uto_page_title );
				$url = $title->getLocalURL();

				$responseData[] = [
					'type' => $row->uto_type,
					'header' => $title ? $title->getSubpageText() : $row->uto_page_title,
					'subheader' => '',
					'body' => '',
					'url' => $url,
					'sortkey' => 100,
					'RLmodules' => [],
					'wiki_id' => $row->uto_wiki_id,
					'page_title' => $row->uto_page_title,
					'page_name' => $this->getPageName( $row->uto_page_title ),
					'namespace' => $this->getNamespaceText( $row->uto_page_title )
				];
			}
		}

		$hookContainer = $this->getHookContainer();
		$hookContainer->run( 'UnifiedTaskOverviewTaskCollectionComplete', [ &$responseData ] );

		return $this->getResponseFactory()->createJson( $responseData );
	}

	/**
	 * Name of the page a task belongs to, without its namespace.
	 *
	 * Most descriptors name the page in their header already, but not all of them do
	 * (a task of SimpleTasks is headed by the task itself), so consumers listing tasks
	 * by page need a name that does not depend on the descriptor.
	 *
	 * @param string $prefixedDBkey
	 * @return string
	 */
	private function getPageName( string $prefixedDBkey ): string {
		$title = Title::newFromDBkey( $prefixedDBkey );
		if ( !$title ) {
			return $prefixedDBkey;
		}

		return $title->getText();
	}

	/**
	 * Namespace a task belongs to, as shown in the list of tasks.
	 *
	 * Tasks of other wikis of a farm are named with the namespaces of the current wiki,
	 * since only their title is stored. Namespace IDs are shared across a farm, so this
	 * only differs for namespaces that were renamed on a single instance.
	 *
	 * @param string $prefixedDBkey
	 * @return string
	 */
	private function getNamespaceText( string $prefixedDBkey ): string {
		$title = Title::newFromDBkey( $prefixedDBkey );
		if ( !$title ) {
			return '';
		}
		if ( $title->getNamespace() === NS_MAIN ) {
			return Message::newFromKey( 'blanknamespace' )->text();
		}

		return $title->getNsText() ?: '';
	}

	/**
	 * Types can be prefixed to support dynamic registration
	 *
	 * @param ManifestAttributeBasedRegistry $registry
	 * @param string $type
	 * @return string|null
	 */
	private function resolveTaskDescriptorClass( ManifestAttributeBasedRegistry $registry, string $type ): ?string {
		foreach ( $registry->getAllKeys() as $prefix ) {
			if ( str_starts_with( $type, $prefix ) ) {
				return $registry->getValue( $prefix );
			}
		}

		return null;
	}
}
