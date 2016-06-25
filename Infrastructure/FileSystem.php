<?php 
namespace Zodream\Infrastructure;
/**
 * 文件操作
 *
 * @author Jason
 * @time 2015-12-1
 */

class FileSystem {

	/**
	 * 遍历文件夹
	 * @param string $dir
	 * @return array
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
	/**
	 * 获取完整路径
	 * @param string $file
	 * @return null|string
	 */
	public static function getFile($file) {
		if(is_file($file)) {
			return $file;
		}
		$vendor = dirname(dirname(dirname(__FILE__)));
		$file   = '/'. ltrim($file, '/');
		if (is_file($vendor.$file)) {
			return $vendor.$file;
		}
		$app = dirname(APP_DIR);
		if (is_file($app.$file)) {
			return $app.$file;
		}
		return null;
	}

	/**
	 * 获取文件内容
	 * @param string $file
	 * @return string
	 */
	public static function read($file) {
		return file_get_contents(self::getFile($file));
	}

	/**
	 * 写入文件
	 * @param string $file
	 * @param string $data
	 * @return int|bool
	 */
	public static function write($file, $data) {
		return file_put_contents($file, $data);
	}

	/**
	 * 复制文件
	 * @param string $res
	 * @param string $des
	 * @return bool
	 */
	public static function copy($res, $des) {
		if (!is_file($res)) {
			return false;
		}
		$resOpen = fopen($res, 'r');
		//定位
		$dir = dirname($des);
		if (!is_dir($dir)) {
			//可创建多级目录 
			mkdir($dir, 0777, true);
		}
		$desOpen = fopen($des, 'w+');
		//边读边写 
		$buffer = 1024;
		while(!feof($resOpen)) {
			$content = fread($resOpen, $buffer);
			fwrite($desOpen, $content);
		}
		fclose($resOpen);
		fclose($desOpen);
		return true;
	}
}