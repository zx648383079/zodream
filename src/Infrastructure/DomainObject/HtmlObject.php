<?php
namespace Zodream\Infrastructure\DomainObject;

interface HtmlObject{
	/**
	 * 执行
	 * @param mixed $args
	 */
	static function execute($args);
}