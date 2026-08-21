<?php

namespace MediaWiki\Extension\UnifiedTaskOverview;

use DateTime;
use MediaWiki\Language\Language;
use MediaWiki\Language\RawMessage;
use MediaWiki\Message\Message;

class StoredTask {

	/**
	 * @param string $id
	 * @param string $type
	 * @param int $pageNamespace
	 * @param string $pageTitle
	 * @param DateTime $created
	 * @param string $wikiId
	 * @param array $data
	 */
	public function __construct(
		public readonly string $id,
		public readonly string $type,
		public readonly int $pageNamespace,
		public readonly string $pageTitle,
		public readonly DateTime $created,
		public readonly string $wikiId,
		public readonly array $data
	) {
	}

	/**
	 * @param Language $language
	 * @return array
	 */
	public function serializeForOutput( Language $language ): array {
		return [
			'type' => $this->type,
			'header' => $this->unserializeMessage( $this->data['header'] )->parse(),
			'subheader' => $this->unserializeMessage( $this->data['sub-header'] )->parse(),
			'body' => $this->unserializeMessage( $this->data['body'] )->parse(),
			'url' => $this->data['url'],
			'sortkey' => $this->data['sortkey'],
			'RLmodules' => $this->data['rlModules'],
			'wiki_id' => $this->wikiId,
			'page_name' => $this->data['page_title'],
			'namespace' => $this->getNsText( $this->pageNamespace, $language ),
		];
	}

	private function getNsText( int $ns, Language $language ): string {
		if ( $ns === NS_MAIN ) {
			return Message::newFromKey( 'blanknamespace' )->text();
		}
		return $language->getNsText( $ns );
	}

	/**
	 * @param mixed $serialized
	 * @return Message
	 */
	private function unserializeMessage( mixed $serialized ): Message {
		$msg = new Message( '' );
		$msg->unserialize( $serialized );
		if ( !$msg->exists() ) {
			return new RawMessage( $msg->getKey(), $msg->getParams() );
		}
		return $msg;
	}

}
