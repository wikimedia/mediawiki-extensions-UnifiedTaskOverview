<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Rest;

use MediaWiki\Extension\UnifiedTaskOverview\ITaskDescriptor;
use MediaWiki\MediaWikiServices;
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
			$isRootWiki = ( $row->uto_wiki_id === '' || $row->uto_wiki_id === 'w' );

			$class = $this->resolveTaskDescriptorClass( $registry, $row->uto_type );
			if ( !$class ) {
				continue;
			}

			if ( $isRootWiki ) {
				/** @var ITaskDescriptor */
				$taskDescriptor = $class::newFromTaskRow( $row );
				if ( !$taskDescriptor ) {
					continue;
				}

				$responseData[] = [
					'type' => $taskDescriptor->getType(),
					'header' => $taskDescriptor->getHeader()->parse(),
					'subheader' => $taskDescriptor->getSubHeader()->text(),
					'body' => $taskDescriptor->getBody()->parse(),
					'url' => $taskDescriptor->getURL(),
					'sortkey' => $taskDescriptor->getSortKey(),
					'RLmodules' => $taskDescriptor->getRLModules(),
					'wiki_id' => $row->uto_wiki_id
				];
			} else {
				$title = Title::newFromDBkey( $row->uto_page_title );
				$instancePath = $this->resolveInstancePath( $row->uto_wiki_id );
				$url = wfScript( 'index' ) . '?' . http_build_query( [
					'title' => $title ? $title->getPrefixedText() : $row->uto_page_title,
					'sfr' => $instancePath
				] );

				$responseData[] = [
					'type' => $row->uto_type,
					'header' => $title ? $title->getSubpageText() : $row->uto_page_title,
					'subheader' => '',
					'body' => '',
					'url' => $url,
					'sortkey' => 100,
					'RLmodules' => [],
					'wiki_id' => $row->uto_wiki_id
				];
			}
		}

		return $this->getResponseFactory()->createJson( $responseData );
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

	/**
	 * Resolve a wiki identifier to the instance path used for sfr routing
	 *
	 * @param string $wikiId
	 * @return string
	 */
	private function resolveInstancePath( string $wikiId ): string {
		$services = MediaWikiServices::getInstance();
		if ( $services->hasService( 'BlueSpiceWikiFarm.WikiMap' ) ) {
			$farmWikiMap = $services->getService( 'BlueSpiceWikiFarm.WikiMap' );
			$instance = $farmWikiMap->getInstanceByWikiId( $wikiId );
			if ( $instance ) {
				return $instance->getPath();
			}
		}
		return $wikiId;
	}

}
