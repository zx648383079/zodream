<?php
namespace App\Lib\Enum;

class ERoute extends EBase {
	/**
	 * 普通类型 通过c和v获取
	 * @var unknown
	 */
	const COMMON    = 0;
	/**
	 * YII 通过r获取
	 * @var unknown
	 */
	const R         = 1;
	/**
	 * 通过.php/获取
	 * @var unknown
	 */
	const PHP       = 2;
	/**
	 * 短链接 通过s获取
	 * @var unknown
	 */
	const SHORT     = 3;
	/**
	 * 优雅链接 /获取
	 * @var unknown
	 */
	const GRACE     = 4;
	/**
	 * 正则获取
	 * @var unknown
	 */
	const REGEX     = 5;
	/**
	 * 配置文件获取
	 * @var unknown
	 */
	const CONFIG    = 6;
	/**
	 * 控制台获取
	 * @var unknown
	 */
	const CLI       = 7;
	
	const __default = self::COMMON;
}