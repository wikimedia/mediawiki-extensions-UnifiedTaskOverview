<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Tag\Handler;

use MediaWiki\Html\Html;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\PPFrame;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;

class MyTasksHandler implements ITagHandler {

	/**
	 * The list itself is built on the client, from the tasks of the current user, so
	 * only the container is rendered here.
	 *
	 * @inheritDoc
	 */
	public function getRenderedContent( string $input, array $params, Parser $parser, PPFrame $frame ): string {
		$attribs = [ 'class' => 'uto-mytasks' ];

		$types = array_filter( (array)( $params['types'] ?? [] ) );
		if ( $types ) {
			$attribs['data-types'] = implode( ',', $types );
		}

		return Html::element( 'div', $attribs );
	}
}
