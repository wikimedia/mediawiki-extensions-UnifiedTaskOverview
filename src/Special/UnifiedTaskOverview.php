<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Special;

use Html;
use OOUI\TextInputWidget;
use SpecialPage;

class UnifiedTaskOverview extends SpecialPage {

	/**
	 */
	public function __construct() {
		parent::__construct( 'UnifiedTaskOverview' );
	}

	/**
	 * @param string $par
	 */
	public function execute( $par ) {
		$output = $this->getOutput();

		$output->addModules( 'ext.unifiedTaskOverview.specialPage' );

		$output->enableOOUI();
		$search = Html::openElement( 'div', [
			'style' => 'width: 100%; text-align: center;'
		] );
		$search .= new TextInputWidget( [
			'infusable' => true,
			'id' => 'taskSearch',
			'icon' => 'search',
			'placeholder' => 'Search',
		] );
		$search .= Html::closeElement( 'div' );
		$output->addHTML( $search );
		$output->addHTML( Html::element( 'div', [ 'id' => 'unifiedTaskOverview-tiles' ] ) );
		$this->setHeaders();
	}
}
