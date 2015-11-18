<?php
namespace App\Lib\File;

class FBase {
	
	
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
	
	public static function reader($file) {
		return file_get_contents(self::getFile($file));
	}
	
	public static function writer($file, $data) {
		file_put_contents($file, $data);
	}
}