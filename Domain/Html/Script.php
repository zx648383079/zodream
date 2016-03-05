<?php 
namespace Zodream\Domain\Html;
/**
 * 加载脚本
 *
 * @author Jason
 * @time 2015-12-1
 */
use Zodream\Domain\Routing\UrlGenerator;

class Script {
	public static function make($files, $dir = 'assets/') {
		$dir = rtrim($dir, '/').'/';
		foreach ($files as $file) {
			if (is_string($file) && !empty($file)) {
				if (!strstr($file,'//')) {
					self::makeWithRelative($file, $dir);
				} else {
					self::makeWithUrl($file);
				}
			} else if (is_object($file)) {
				$file();
			}
		}
	}
	
	private static function makeWithRelative($file, $dir) {
		$needDeal = true;
		if (substr($file, 0, 1) === '@') {
			$needDeal = false;
			$file = substr($file, 1);
		}
		$file = ltrim($file, '/');
		if (stristr($file, '.css')) {
			self::makeCss(UrlGenerator::to($dir.($needDeal ? 'css/' : '').$file));
		} elseif (stristr($file, '.js')) {
			self::makeJs(UrlGenerator::to($dir.($needDeal ? 'js/' : '').$file));
		} else {
			self::makeJs(UrlGenerator::to($dir.($needDeal ? 'js/' : '').$file. '.js'));
		}
	}
	
	private static function makeWithUrl($file) {
		if (stristr($file, '.css')) {
			self::makeCss($file);
		} elseif (stristr($file, '.js')) {
			self::makeJs($file);
		} else {
			self::makeJs($file.'.js');
		}
	}
	
	private static function makeCss($file) {
		echo '<link rel="stylesheet" type="text/css" href="'.$file.'"/>';
	}
	
	private static function makeJs($file) {
		echo '<script src="'.$file.'"></script>';
	}
	
}