<?php
namespace Zodream\Infrastructure\DomainObject;

interface ValidaterObject {
	/**
	 * 验证
	 * @param unknown $param
	 * @return boolean
	 */
	static function make($param);
}