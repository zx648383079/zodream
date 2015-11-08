<?php
namespace App\Lib\Helper;

use App;

final class HCache implements IBase {
	public static function make($name, $content, $life = 0) {
		$path    = APP_DIR.App::config('cache.path').$name.'.php';
		$content = '<?php if (!defined(\'APP_DIR\')) exit(\'NO THING!\');?>'.$content;
		file_put_contents($path, $content);
	}	
	
	public static function show($name) {
		$path = APP_DIR.App::config('cache.path').$name.'.php';
		if (is_file($path)) {
			return file_get_contents($path);
		}
		return FALSE;
	}
	
	
}