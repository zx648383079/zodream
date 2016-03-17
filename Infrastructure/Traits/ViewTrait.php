<?php
namespace Zodream\Infrastructure\Traits;

use Zodream\Domain\Response\View;
trait ViewTrait {
	/**
	 * 传递数据
	 *
	 * @param string|array $key 要传的数组或关键字
	 * @param string $value  要传的值
	 */
	protected function send($key, $value = null) {
		View::getInstance()->set($key, $value);
	}

	protected function getData($key) {
		View::getInstance()->get($key);
	}
	
	/**
	 * 加载视图
	 *
	 * @param string $name 视图的文件名
	 * @param array $data 要传的数据
	 */
	protected function show($name = null, $data = null) {
		View::getInstance()->show($name, $data);
	}
}