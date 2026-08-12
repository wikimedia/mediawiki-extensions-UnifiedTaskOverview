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
		return Html::element( 'div', [ 'class' => 'uto-mytasks' ] );
	}
}
