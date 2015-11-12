<?php
/****************************************************
*控制器基类
*
*
*******************************************************/
namespace App\Controller;

use App;
use App\Lib\Lang;
use App\Lib\Validation;
use App\Lib\Loader;
use App\Lib\Role\RVerify;

class Controller {
	protected $loader;
	
	function __construct($loader = null) {
		App::$response->set('title', App::config('App'));
		App::$response->set('lang', Lang::$language);
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
		return true;
	}

	/**
	* 验证数据
	*
	* @param array $request 要验证的数据
	* @param array $param 验证的规则
	* @return array
	*/
	function validata( $request, $param) {
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
	function send($key, $value = null) {
		App::$response->set($key, $value);
	}
	
	function component($name = 'index', $data = null) {
		return App::$response->component($name, $data);
	}

	/**
	* 加载视图
	*
	* @param string $name 视图的文件名
	* @param array $data 要传的数据
	*/
	function show($name = "index", $data = null) {
		App::$response->show($name, $data);
	}

	/**
	* 返回JSON数据
	*
	* @param array|string $data 要传的值
	* @param string $type 返回类型
	*/
	function ajaxJson($data, $type = 'JSON') {
		App::$response->ajaxJson($data, $type);
	}

	/**
	* 显示图片
	*
	* @param $img
	*/
	function showImg($img) {
		App::$response->showImg($img);
	}
}