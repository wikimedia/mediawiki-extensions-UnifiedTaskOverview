<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Tag;

use MediaWiki\Extension\UnifiedTaskOverview\Tag\Handler\MyTasksHandler;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\FormEngine\StandaloneFormSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\GenericTagHandler\MarkerType;
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordListValue;

/**
 * Lists the open tasks (workflows, simple tasks, read confirmations, ...) the current
 * user is assigned to, from all namespaces and all wikis. The optional "types" attribute
 * restricts the list to the given task types.
 */
class MyTasks extends GenericTag {

	private const TYPES = [
		'workflow' => 'unifiedtaskoverview-mytasks-type-workflows',
		'task' => 'unifiedtaskoverview-mytasks-type-tasks',
		'readconfirmation' => 'unifiedtaskoverview-mytasks-type-readconfirmation',
	];

	/**
	 * @inheritDoc
	 */
	public function getTagNames(): array {
		return [ 'mytasks' ];
	}

	/**
	 * @inheritDoc
	 */
	public function hasContent(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getMarkerType(): MarkerType {
		return new MarkerType\NoWiki();
	}

	/**
	 * @inheritDoc
	 */
	public function getContainerElementName(): ?string {
		return 'div';
	}

	/**
	 * @inheritDoc
	 */
	public function getHandler( MediaWikiServices $services ): ITagHandler {
		return new MyTasksHandler();
	}

	/**
	 * @inheritDoc
	 */
	public function getParamDefinition(): ?array {
		return [
			'types' => ( new KeywordListValue() )
				->setKeywords( array_keys( self::TYPES ) )
				->setListSeparator( ',' )
				->setDefaultValue( [] ),
		];
	}

	/**
	 * The rendered markup is an empty container only - the actual data is loaded
	 * client-side for the viewing user, so the parser cache can be kept.
	 *
	 * @inheritDoc
	 */
	public function shouldDisableParserCache(): bool {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function getResourceLoaderModules(): ?array {
		return [ 'ext.unifiedTaskOverview.tag.mytasks' ];
	}

	/**
	 * @inheritDoc
	 */
	public function getResourceLoaderModuleStyles(): ?array {
		return [ 'ext.unifiedTaskOverview.tag.mytasks.styles' ];
	}

	/**
	 * The inspector lets editors pick which task types the list should show.
	 *
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'menutag_multiselect',
				'name' => 'types',
				'labelAlign' => 'top',
				'label' => Message::newFromKey( 'unifiedtaskoverview-mytasks-attr-types-label' )->text(),
				'help' => Message::newFromKey( 'unifiedtaskoverview-mytasks-attr-types-help' )->text(),
				'options' => array_map( static function ( string $key, string $msgKey ): array {
					return [
						'data' => $key,
						'label' => Message::newFromKey( $msgKey )->text(),
					];
				}, array_keys( self::TYPES ), array_values( self::TYPES ) ),
				'widget_allowArbitrary' => false,
				'widget_$overlay' => true,
			],
		] );

		return new ClientTagSpecification(
			'MyTasks',
			Message::newFromKey( 'unifiedtaskoverview-mytasks-desc' ),
			$formSpec,
			Message::newFromKey( 'unifiedtaskoverview-mytasks-title' ),
			'mytasks'
		);
	}
}
