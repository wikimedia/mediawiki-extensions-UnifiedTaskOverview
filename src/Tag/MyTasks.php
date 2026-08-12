<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\Tag;

use MediaWiki\Extension\UnifiedTaskOverview\Tag\Handler\MyTasksHandler;
use MediaWiki\MediaWikiServices;
use MediaWiki\Message\Message;
use MWStake\MediaWiki\Component\GenericTagHandler\ClientTagSpecification;
use MWStake\MediaWiki\Component\GenericTagHandler\GenericTag;
use MWStake\MediaWiki\Component\GenericTagHandler\ITagHandler;
use MWStake\MediaWiki\Component\GenericTagHandler\MarkerType;

/**
 * Lists all tasks (workflows, simple tasks, read confirmations, ...) the current user
 * is assigned to. The tag takes no attributes - it always lists all tasks, from all
 * namespaces and all wikis.
 */
class MyTasks extends GenericTag {

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
		return [];
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
	 * No form specification: the tag is inserted directly, there is nothing to configure.
	 *
	 * @inheritDoc
	 */
	public function getClientTagSpecification(): ClientTagSpecification|null {
		return new ClientTagSpecification(
			'MyTasks',
			Message::newFromKey( 'unifiedtaskoverview-mytasks-desc' ),
			null,
			Message::newFromKey( 'unifiedtaskoverview-mytasks-title' ),
			'mytasks'
		);
	}
}
