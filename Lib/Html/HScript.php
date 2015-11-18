<?php 
namespace App\Lib\Html;

use App\Lib\Helper\HUrl;

class HScript implements IBase {
	public static function make($files) {
		foreach ($files as $file) {
			if (is_string($file) && !empty($file)) {
				$result = '';
				if (!strstr($file,'//')) {
					if (stristr($file, '.css')) {
						$result = '<link rel="stylesheet" type="text/css" href="'.HUrl::file('asset/css/'.$file).'"/>';
					} elseif (stristr($file, '.js')) {
						$result = '<script src="'.HUrl::file('asset/js/'.$file).'"></script>';
					} else {
						$result = '<script src="'.HUrl::file('asset/js/'.$file).'.js"></script>';
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