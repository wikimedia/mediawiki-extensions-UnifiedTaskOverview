<?php

namespace MediaWiki\Extension\UnifiedTaskOverview\ContentDroplets;

use MediaWiki\Extension\ContentDroplets\Droplet\TagDroplet;
use MediaWiki\Message\Message;

class MyTasksDroplet extends TagDroplet {

	/**
	 * @inheritDoc
	 */
	public function getName(): Message {
		return Message::newFromKey( 'unifiedtaskoverview-mytasks-title' );
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): Message {
		return Message::newFromKey( 'unifiedtaskoverview-mytasks-desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return 'droplet-mytasks';
	}

	/**
	 * @inheritDoc
	 */
	public function getRLModules(): array {
		return [ 'ext.unifiedTaskOverview.tag.mytasks.styles' ];
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
		return [];
	}

	/**
	 * @return bool
	 */
	protected function hasContent(): bool {
		return false;
	}

	/**
	 * @return string|null
	 */
	public function getVeCommand(): ?string {
		return 'mytasksCommand';
	}
}
