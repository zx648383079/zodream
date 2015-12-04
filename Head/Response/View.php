<?php 
namespace Zodream\Head;
/*
* 响应
* 
* @author Jason
* @time 2015-11.29
*/
use Zodream\Body\Object\Obj;
use Zodream\Body\Html\View;

final class View extends Obj {
	private static $_instance;
	/**
	 * 单例模式
	 * @return \Zodream\Head\Response
	 */
	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	 * 获取额外的值 主要是js、css文件或脚本
	 * @return string
	 */
	public function getExtra() {
		return $this->get('_extra');
	}
	
	/**
	 * 在视图中包含其他视图的方法
	 * @param string|array $names 视图文件名
	 * @param string|array $param 传给视图的内容
	 * @param string $replace 是否替换
	 */
	public function extend($names, $param = null, $replace = TRUE) {
		if (!$replace) {
			$param = array_merge((array)$this->getExtra(), (array)$param);
		}
		$this->set('_extra', $param);
		foreach (OArray::to($names, '.') as $value) {
			$file = View::make($value);
			if (file_exists($file)) {
				include($file);
			} else {
				echo $file,' 不存在！';
				var_dump($this->get());
			}
		}
	}
	
	
	
	/**
	 * 加载视图
	 *
	 * @param string $name 视图的文件名
	 * @param array $data 要传的数据
	 */
	public function show($name = "index", $data = null) {
		if (!empty($data)) {
			$this->set($data);
		}
		if (APP_API) {
			$this->ajaxJson($this->get());
		} else {
			ob_start();
			include(View::make($name));
			$content = ob_get_contents();
			ob_end_clean();
			$this->showGzip($content);
		}
	}
	
	public function showGzip($content) {
		if (extension_loaded('zlib')) {
			if (!headers_sent() && isset($_SERVER['HTTP_ACCEPT_ENCODING']) &&
					strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE) {
						ob_start('ob_gzhandler');
					} else {
						ob_start();
					}
		} else {
			ob_start();
		}
		header( 'Content-Type:text/html;charset=utf-8' );
		ob_implicit_flush(FALSE);
		echo $content;
		ob_end_flush();
		exit;
	}
}