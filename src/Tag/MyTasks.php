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
use MWStake\MediaWiki\Component\InputProcessor\Processor\KeywordValue;

/**
 * Lists the open tasks of one type the current user is assigned to, from all namespaces
 * and all wikis. Which type is chosen by the droplet the tag is inserted with.
 */
class MyTasks extends GenericTag {

	public const TYPES = [
		'task' => 'simple-tasks-task',
		'workflow' => 'workflows-activity-',
		'readconfirmation' => 'page-read-confirmation',
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
			'type' => ( new KeywordValue() )
				->setKeywords( array_keys( self::TYPES ) )
				->setDefaultValue( 'task' ),
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
	 * The droplets insert the tag for a type of task each, and the inspector lets
	 * editors pick a different one afterwards.
	 *
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		$formSpec = new StandaloneFormSpecification();
		$formSpec->setItems( [
			[
				'type' => 'dropdown',
				'name' => 'type',
				'label' => Message::newFromKey( 'unifiedtaskoverview-mytasks-attr-type-label' )->text(),
				'value' => 'task',
				'options' => array_map( static function ( string $key ): array {
					// The following messages are used here:
					// * unifiedtaskoverview-mytasks-type-task
					// * unifiedtaskoverview-mytasks-type-workflow
					// * unifiedtaskoverview-mytasks-type-readconfirmation
					return [
						'data' => $key,
						'label' => Message::newFromKey( "unifiedtaskoverview-mytasks-type-$key" )->text(),
					];
				}, array_keys( self::TYPES ) ),
				'widget_$overlay' => true,
			],
		] );

		return new ClientTagSpecification(
			'MyTasks',
			Message::newFromKey( 'unifiedtaskoverview-mytasks-desc' ),
			$formSpec,
			Message::newFromKey( 'unifiedtaskoverview-mytasks-title' )
		);
	}
}
