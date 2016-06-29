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
	 * 获取文件的拓展名
	 * @param string $file
	 * @param bool $point 是否带点
	 * @return string
	 */
	public static function getExtension($file, $point = false) {
		$arg = strtolower(substr(strrchr($file, '.'), 1));
		if (empty($arg) || !$point) {
			return $arg;
		}
		return '.'.$arg;
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
	 * 建立文件夹
	 *
	 * @param string $aimUrl
	 * @return bool
	 */
	public static function createDir($aimUrl) {
		$aimUrl = str_replace('', '/', $aimUrl);
		$aimDir = '';
		$arr = explode('/', $aimUrl);
		$result = true;
		foreach ($arr as $str) {
			$aimDir .= $str . '/';
			if (!is_dir($aimDir)) {
				$result = mkdir($aimDir);
			}
		}
		return $result;
	}

	/**
	 * 建立文件
	 *
	 * @param string $aimUrl
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function createFile($aimUrl, $overWrite = false) {
		if (is_file($aimUrl) && $overWrite == false) {
			return false;
		} elseif (is_file($aimUrl) && $overWrite == true) {
			self::unlinkFile($aimUrl);
		}
		$aimDir = dirname($aimUrl);
		self::createDir($aimDir);
		touch($aimUrl);
		return true;
	}

	/**
	 * 移动文件夹
	 *
	 * @param string $oldDir
	 * @param string $aimDir
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function moveDir($oldDir, $aimDir, $overWrite = false) {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
		$oldDir = str_replace('', '/', $oldDir);
		$oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
		if (!is_dir($oldDir)) {
			return false;
		}
		if (!is_dir($aimDir)) {
			self::createDir($aimDir);
		}
		@ $dirHandle = opendir($oldDir);
		if (!$dirHandle) {
			return false;
		}
		while (false !== ($file = readdir($dirHandle))) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			if (!is_dir($oldDir . $file)) {
				self::moveFile($oldDir . $file, $aimDir . $file, $overWrite);
			} else {
				self::moveDir($oldDir . $file, $aimDir . $file, $overWrite);
			}
		}
		closedir($dirHandle);
		return rmdir($oldDir);
	}

	/**
	 * 移动文件
	 *
	 * @param string $fileUrl
	 * @param string $aimUrl
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function moveFile($fileUrl, $aimUrl, $overWrite = false) {
		if (!is_file($fileUrl)) {
			return false;
		}
		if (is_file($aimUrl) && $overWrite = false) {
			return false;
		} elseif (is_file($aimUrl) && $overWrite = true) {
			self::unlinkFile($aimUrl);
		}
		$aimDir = dirname($aimUrl);
		self::createDir($aimDir);
		rename($fileUrl, $aimUrl);
		return true;
	}

	/**
	 * 删除文件夹
	 *
	 * @param string $aimDir
	 * @return boolean
	 */
	public static function unlinkDir($aimDir) {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
		if (!is_dir($aimDir)) {
			return false;
		}
		$dirHandle = opendir($aimDir);
		while (false !== ($file = readdir($dirHandle))) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			if (!is_dir($aimDir . $file)) {
				self::unlinkFile($aimDir . $file);
			} else {
				self::unlinkDir($aimDir . $file);
			}
		}
		closedir($dirHandle);
		return rmdir($aimDir);
	}

	/**
	 * 删除文件
	 *
	 * @param string $aimUrl
	 * @return boolean
	 */
	public static function unlinkFile($aimUrl) {
		if (file_exists($aimUrl)) {
			unlink($aimUrl);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 复制文件夹
	 *
	 * @param string $oldDir
	 * @param string $aimDir
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function copyDir($oldDir, $aimDir, $overWrite = false) {
		$aimDir = str_replace('', '/', $aimDir);
		$aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
		$oldDir = str_replace('', '/', $oldDir);
		$oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
		if (!is_dir($oldDir)) {
			return false;
		}
		if (!is_dir($aimDir)) {
			self::createDir($aimDir);
		}
		$dirHandle = opendir($oldDir);
		while (false !== ($file = readdir($dirHandle))) {
			if ($file == '.' || $file == '..') {
				continue;
			}
			if (!is_dir($oldDir . $file)) {
				self::copyFile($oldDir . $file, $aimDir . $file, $overWrite);
			} else {
				self:: copyDir($oldDir . $file, $aimDir . $file, $overWrite);
			}
		}
		return closedir($dirHandle);
	}

	/**
	 * 复制文件
	 *
	 * @param string $fileUrl
	 * @param string $aimUrl
	 * @param boolean $overWrite 该参数控制是否覆盖原文件
	 * @return boolean
	 */
	public static function copyFile($fileUrl, $aimUrl, $overWrite = false) {
		if (!is_file($fileUrl)) {
			return false;
		}
		if (is_file($aimUrl) && $overWrite == false) {
			return false;
		} elseif (is_file($aimUrl) && $overWrite == true) {
			self::unlinkFile($aimUrl);
		}
		$aimDir = dirname($aimUrl);
		self::createDir($aimDir);
		copy($fileUrl, $aimUrl);
		return true;
	}
}