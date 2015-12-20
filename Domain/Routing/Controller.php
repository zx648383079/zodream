<?php
namespace Zodream\Domain\Routing;
/**
 * 控制器基类
 *
 * @author Jason
 * @time 2015-12-19
 */
use Zodream\Infrastructure\Loader;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Language;
use Zodream\Infrastructure\Response\Theme;
use Zodream\Infrastructure\Response\Component;
use Zodream\Infrastructure\Response\Ajax;
use Zodream\Infrastructure\Response\Image;
use Zodream\Infrastructure\Traits\LoaderTrait;
use Zodream\Infrastructure\Traits\FilterTrait;
use Zodream\Infrastructure\Traits\ValidataTrait;

abstract class Controller {
	
	use LoaderTrait, FilterTrait, ValidataTrait;
	
	function __construct($loader = null) {
		$this->send(array(
				'title'    => Config::getInstance()->get('app.title'),
				'language' => Language::getLang()
		));
		$this->loader = $loader instanceof Loader ? $loader : new Loader();
	}
	
	/**
	 * 传递数据
	 *
	 * @param string|array $key 要传的数组或关键字
	 * @param string $value  要传的值
	 */
	protected function send($key, $value = null) {
		Theme::getInstance()->set($key, $value);
	}
	
	protected function component($name = 'index', $data = null) {
		return Component::view($name, $data);
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
	
	/**
	 * 返回JSON数据
	 *
	 * @param array|string $data 要传的值
	 * @param string $type 返回类型
	 */
	protected function ajaxJson($data, $type = 'JSON') {
		Ajax::view($data, $type);
	}
	
	/**
	 * 显示图片
	 *
	 * @param $img
	 */
	protected function image($img) {
		Image::view($img);
	}
}