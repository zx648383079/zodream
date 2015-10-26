<?php 
namespace App\Lib\Html;

use App;

class HView implements IBase
{
	private static $dir;
	
	private static $ext;
	
	private static function loadConfig()
	{
		if( empty($dir) ) {
			$configs = App::config('view');
			self::$dir = isset($configs['dir']) ? $configs['dir'] : 'app/view';
			self::$ext = isset($configs['ext']) ? $configs['ext'] : '.php';
			if(substr( self::$ext , 0, 1 ) != '.')
			{
				self::$ext = '.' . self::$ext;
			}
		}
	}
	
	public static function make($name)
	{
		self::loadConfig();
		$name = str_replace('.', '/', $name);
		$file = APP_DIR . '/' . self::$dir. '/'. $name;
		$file = str_replace( '//', '/', $file);
		
		return $file . self::$ext;
	}
}