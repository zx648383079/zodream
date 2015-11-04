<?php 
namespace App\Lib\Html;

use App\Lib\Helper\HUrl;

class HScript implements IBase {
	public static function make($files) {
		foreach ($files as $file) {
			if (is_string($file) && !empty($file)) {
				$result = '';
				if (!strstr($file,'://')) {
					$arr = explode('.', $file);
					switch (end($arr)) {
						case 'js':
							$result = '<script src="'.HUrl::to('asset/js/'.$file).'"></script>';
							break;
						case 'css':
							$result = '<link rel="stylesheet" type="text/css" href="'.HUrl::to('asset/css/'.$file).'"/>';
							break;
						default:
							$result = '<script src="'.HUrl::to('asset/js/'.$file).'.js"></script>';
							break;
					}
					echo $result;
				} else {
					$arr = explode('.', $file);
					switch (end($arr)) {
						case 'js':
							$result = '<script src="'.$file.'"></script>';
							break;
						case 'css':
							$result = '<link rel="stylesheet" type="text/css" href="'.$file.'"/>';
							break;
						default:
							$result = '<script src="'.$file.'"></script>';
							break;
					}
				}
			} else if (is_object($file)) {
				$file();
			}
		}
	}
}