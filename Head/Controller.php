<?php
namespace Zodream\Head;
/**
 * 控制器基类
 *
 * @author Jason
 * @time 2015-12-1
 */
use Zodream\Body\Config;
use Zodream\Body\Language;
use Zodream\Body\Loader;

abstract class Controller {
	protected $loader;
	
	function __construct($loader = null) {
		$this->send(array(
				'title'    => Config::getInstance()->get('app.title'),
				'language' => Language::getLang()
		));
		$this->loader = $loader instanceof Loader ? $loader : new Loader();
	}
	
	public function __get($key) {
		return $this->loader->get($key);
	}
	
	public function __set($key, $value) {
		$this->loader->set($key, $value);
	}
	
	/**
	 * 在执行之前做规则验证
	 * @param string $func 方法名
	 * @return boolean
	 */
	public function before($func) {
		if (isset($this->rules)) {
			$func = str_replace(APP_ACTION, '', $func);
			$role = isset($this->rules['*']) ? $this->rules['*'] : '';
			$role = isset($this->rules[$func]) ? $this->rules[$func] : $role;
			return RVerify::make($role);
		}
		if (method_exists($this, '_initialize')) {
			$this->_initialize();
		}
		return TRUE;
	}
	
	/**
	 * 验证数据
	 *
	 * @param array $request 要验证的数据
	 * @param array $param 验证的规则
	 * @return array
	 */
	protected function validata( $request, $param) {
		$vali   = new Validation();
		$result = $vali->make($request, $param);
		if (!$result) {
			$result = $vali->error;
		}
		return $result;
	}
	
	/**
	 * 传递数据
	 *
	 * @param string|array $key 要传的数组或关键字
	 * @param string $value  要传的值
	 */
	protected function send($key, $value = null) {
		Response::getInstance()->set($key, $value);
	}
	
	protected function component($name = 'index', $data = null) {
		return Response::getInstance()->component($name, $data);
	}
	
	/**
	 * 加载视图
	 *
	 * @param string $name 视图的文件名
	 * @param array $data 要传的数据
	 */
	protected function show($name = "index", $data = null) {
		Response::getInstance()->show($name, $data);
	}
	
	/**
	 * 返回JSON数据
	 *
	 * @param array|string $data 要传的值
	 * @param string $type 返回类型
	 */
	protected function ajaxJson($data, $type = 'JSON') {
		Response::getInstance()->ajaxJson($data, $type);
	}
	
	/**
	 * 显示图片
	 *
	 * @param $img
	 */
	protected function image($img) {
		Response::getInstance()->image($img);
	}
}