<?php
namespace Zodream\Head\Response;

use Zodream\Body\Config;
use Zodream\Head\Url;
defined('THEME_DIR') or define('THEME_DIR', '/Theme/' .Config::theme('dir').'/');

class Theme extends View {
	
	function find() {
	
	}
	
	public function asset($file, $isTheme = TRUE) {
		if ($isTheme) {
			$file = THEME_DIR.ltrim($file, '/');
		}
		echo Url::to($file);
	}
	
	public function url($url) {
		echo Url::to($url);
	}
}