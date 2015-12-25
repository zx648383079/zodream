<?php
namespace Zodream\Domain\Response;

use Zodream\Infrastructure\FileSystem;
class Component {
	protected static $components;
	
	/**
	 * 按部件加载视图
	 * @param string $name
	 * @param string $data
	 * @return self
	 */
	public static function view($name = 'index', $data = null) {
		extract($data);
		ob_start();
		include(FileSystem::view($name));
		self::$components .= ob_get_contents();
		ob_end_clean();
		return self;
	}
	
	/**
	 * 结束并释放视图
	 */
	public static function render() {
		return self::$components;
	}
}