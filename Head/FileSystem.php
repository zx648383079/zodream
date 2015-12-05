<?php 
namespace Zodream\Head;
/**
 * 文件操作
 *
 * @author Jason
 * @time 2015-12-1
 */
use Zodream\Body\Config; 

class FileSystem {
	
	/**
	 * 生成视图路径
	 * @param unknown $name
	 * @return string
	 */
	public static function view($name) {
		$name = str_replace('.', '/', $name);
		$file = APP_DIR .'Theme/' .Config::theme('dir').'/'.$name;
		$file = str_replace( '//', '/', $file);
	
		return $file . Config::theme('ext');
	}
	
	/**
	 * 遍历文件夹
	 */
	public static function findDir($dir) {
		$files    = array();
		$dir_list = @scandir($dir);
		foreach($dir_list as $file) {
			if ( $file != ".." && $file != "." ) {
				if (is_dir($dir . $file)) {
					$files = array_merge($files, self::findDir($dir. $file. '/'));
				} else {
					$files[] = $dir.$file;
				}
			}
		}
		return $files;
	}
	
	public static function getFile($file) {
		if(file_exists($file)) {
			return $file;
		}
		$vendor = dirname(dirname(dirname(__FILE__)));
		$file   = '/'. ltrim($file, '/');
		if (file_exists($vendor.$file)) {
			return $vendor.$file;
		}
		$app = dirname(APP_DIR);
		if (file_exists($app.$file)) {
			return $app.$file;
		}
	}
	
	public static function read($file) {
		return file_get_contents(self::getFile($file));
	}
	
	public static function writ($file, $data) {
		file_put_contents($file, $data);
	}
}