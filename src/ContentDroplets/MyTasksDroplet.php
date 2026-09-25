<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use MediaWiki\Message\Message;

/**
 * Inserts the <mytasks> tag for one type of task. Every type is registered as a droplet
 * of its own, so editors pick the list they want instead of configuring it.
 */
class MyTasksDroplet extends TagDroplet {

	/**
	 * @param string $type One of the keys of MyTasks::TYPES
	 */
	public function __construct(
		private readonly string $type = 'task'
	) {
	}

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		// The following messages are used here:
		// * unifiedtaskoverview-droplet-mytasks-task
		// * unifiedtaskoverview-droplet-mytasks-readconfirmation
		return Message::newFromKey( "unifiedtaskoverview-droplet-mytasks-{$this->type}" );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		// The following messages are used here:
		// * unifiedtaskoverview-droplet-mytasks-task-desc
		// * unifiedtaskoverview-droplet-mytasks-readconfirmation-desc
		return Message::newFromKey( "unifiedtaskoverview-droplet-mytasks-{$this->type}-desc" );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return "droplet-mytasks-{$this->type}";
	}

	/**
	 * @return array
	 */
	public function getCategories(): array {
		return [ 'content', 'lists', 'data' ];
	}

	/**
	 * @return string
	 */
	protected function getTagName(): string {
		return 'mytasks';
	}

	/**
	 * @return array
	 */
	protected function getAttributes(): array {
		return [ 'type' => $this->type ];
	}

	/**
	 * @return bool
	 */
	protected function hasContent(): bool {
		return false;
	}

	/**
	 * @return array
	 */
	public function getRLModules(): array {
		return [ 'ext.unifiedTaskOverview.tag.mytasks.styles' ];
	}

	/**
	 * @return string|null
	 */
	public function getVeCommand(): ?string {
		return 'mytasksCommand';
	}
}
