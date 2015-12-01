<?php 
namespace App\Head;
/*
 * 文件操作
 *
 * @author Jason
 * @time 2015-12-1
 */
class FileSystem {
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