<?php 
namespace Zodream\Body\Html;
/**
 * 加载脚本
 *
 * @author Jason
 * @time 2015-12-1
 */
use Zodream\Head\Url;

class Script {
	public static function make($files, $dir = 'assets/') {
		foreach ($files as $file) {
			if (is_string($file) && !empty($file)) {
				$result = '';
				$dir = rtrim($dir, '/').'/';
				if (!strstr($file,'//')) {
					if (stristr($file, '.css')) {
						$result = '<link rel="stylesheet" type="text/css" href="'.Url::to($dir.'css/'.$file).'"/>';
					} elseif (stristr($file, '.js')) {
						$result = '<script src="'.Url::to($dir.'js/'.$file).'"></script>';
					} else {
						$result = '<script src="'.Url::to($dir.'js/'.$file.'.js').'"></script>';
					}
				} else {
					if (stristr($file, '.css')) {
						$result = '<link rel="stylesheet" type="text/css" href="'.$file.'"/>';
					} elseif (stristr($file, '.js')) {
						$result = '<script src="'.$file.'"></script>';
					} else {
						$result = '<script src="'.$file.'.js"></script>';
					}
				}
				echo $result;
			} else if (is_object($file)) {
				$file();
			}
		}
	}
}