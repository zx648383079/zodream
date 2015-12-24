<?php
namespace Zodream\Infrastructure\DomainObject;

interface ValidaterObject {
	/**
	 * 验证
	 * @param array $param
	 * @return boolean
	 */
	static function make($param);
}