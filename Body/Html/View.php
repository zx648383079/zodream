<?php
namespace App\Body\Html;
/*
 * 视图文件路径
 *
 * @author Jason
 * @time 2015-12-1
 */
class View {
	private static $dir;
	
	private static $ext;
	
	/**
	 * 加载配置文件
	 */
	private static function loadConfig() {
		if (empty($dir)) {
			$configs   = App::config('view');
			self::$dir = isset($configs['dir']) ? $configs['dir'] : 'app/view';
			self::$ext = isset($configs['ext']) ? $configs['ext'] : '.php';
			if (substr(self::$ext, 0, 1) != '.') {
				self::$ext = '.' . self::$ext;
			}
		}
	}
	
	/**
	 * 生成视图路径
	 * @param unknown $name
	 * @return string
	 */
	public static function make($name) {
		self::loadConfig();
		$name = str_replace('.', '/', $name);
		$file = APP_DIR . '/' . self::$dir. '/'. $name;
		$file = str_replace( '//', '/', $file);
	
		return $file . self::$ext;
	}
	
	
}