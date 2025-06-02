<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Special;

use MediaWiki\Html\Html;
use OOJSPlus\Special\OOJSCardSpecialPage;

class UnifiedTaskOverview extends OOJSCardSpecialPage {

	/**
	 */
	public function __construct() {
		parent::__construct( 'UnifiedTaskOverview' );
	}

	/**
	 * @param string $par
	 */
	public function doExecute( $par ) {
		$this->requireNamedUser( 'unifiedtaskoverview-no-login-text' );
		$output = $this->getOutput();

		$output->addModules( 'ext.unifiedTaskOverview.specialPage' );
		$output->addHTML( Html::element( 'div', [ 'id' => 'unifiedTaskOverview-tiles' ] ) );
		$this->setHeaders();
	}
}
