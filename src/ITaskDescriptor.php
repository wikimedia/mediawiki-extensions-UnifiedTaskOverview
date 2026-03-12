<?php

namespace MediaWiki\Extension\UnifiedTaskOverview;

use MediaWiki\Message\Message;
use MediaWiki\Title\Title;
use stdClass;

interface ITaskDescriptor {

	public static function newFromTaskRow( stdClass $row ): ?static;

	/**
	 * @return string
	 */
	public function getUniqueKey(): string;

	/**
	 * @return Title
	 */
	public function getTitle(): Title;

	/**
	 * @return string
	 */
	public function getType(): string;

	/**
	 * @return string
	 */
	public function getURL(): string;

	/**
	 * @return Message
	 */
	public function getHeader(): Message;

	/**
	 * @return Message
	 */
	public function getSubHeader(): Message;

	/**
	 * @return Message
	 */
	public function getBody(): Message;

	/**
	 * @return int
	 */
	public function getSortKey(): int;

	/**
	 * @return array
	 */
	public function getRLModules(): array;
}
