<?php 
namespace App\Lib\File;

class FDir implements IBase
{
	/**
	* 遍历文件夹
	*/
    public static function findDir($dir)
	{
		$files = array();
		$dir_list = @scandir($dir);
		foreach($dir_list as $file)
		{
			if ( $file != ".." && $file != "." )
			{
				if ( is_dir($dir . $file) ){
					$files = array_merge($files, self::findDir($dir . $file . '/'));
				}
				else
				{
					$files[] = $dir .  $file;
				}
			}
		}
		
		return $files;
	}
}