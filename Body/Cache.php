<?php 
namespace Zodream\Body;
/*
* 缓存类
* 
* @author Jason
* @time 2015-11.29
*/

class Cache {
	/**
	 * 设置
	 * @param unknown $name
	 * @param unknown $content
	 * @param number $life
	 */
	public static function set($name, $content, $life = 0) {
		$path    = APP_DIR.App::config('cache.path').$name.'.php';
		$content = '<?php if (!defined(\'APP_DIR\')) exit(\'NO THING!\');?>'.$content;
		file_put_contents($path, $content);
	}
	
	/**
	 * 获取
	 * @param unknown $name
	 */
	public static function get($name) {
		$path = APP_DIR.App::config('cache.path').$name.'.php';
		if (is_file($path)) {
			return file_get_contents($path);
		}
		return FALSE;
	}
}