<?php 
namespace Zodream\Body\Html;
/**
 * 加载脚本
 *
 * @author Jason
 * @time 2015-12-1
 */
use Zodream\Head\Url;
defined('THEME_DIR') or define('THEME_DIR', '/');

class Script {
	public static function make($files) {
		foreach ($files as $file) {
			if (is_string($file) && !empty($file)) {
				$result = '';
				if (!strstr($file,'//')) {
					if (stristr($file, '.css')) {
						$result = '<link rel="stylesheet" type="text/css" href="'.Url::to(THEME_DIR.'css/'.$file).'"/>';
					} elseif (stristr($file, '.js')) {
						$result = '<script src="'.Url::to(THEME_DIR.'js/'.$file).'"></script>';
					} else {
						$result = '<script src="'.Url::to(THEME_DIR.'js/'.$file.'.js').'"></script>';
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