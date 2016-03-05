<?php
namespace Zodream\Infrastructure\Traits;

use Zodream\Infrastructure\Response\Theme;
trait ThemeTrait {
	/**
	 * 传递数据
	 *
	 * @param string|array $key 要传的数组或关键字
	 * @param string $value  要传的值
	 */
	protected function send($key, $value = null) {
		Theme::getInstance()->set($key, $value);
	}
	
	/**
	 * 加载视图
	 *
	 * @param string $name 视图的文件名
	 * @param array $data 要传的数据
	 */
	protected function show($name = null, $data = null) {
		Theme::getInstance()->show($name, $data);
	}
}