<?php
namespace Zodream\Head\Response;

use Zodream\Body\Config;
use Zodream\Head\Url;
use Zodream\Body\Html\Script;
use Zodream\Body\Object\Arr;

defined('THEME_DIR') or define('THEME_DIR', '/Theme/' .Config::theme('dir').'/');

class Theme extends View {
	
	function find() {
	
	}
	
	/**
	 * 输出脚本
	 */
	public function jcs() {
		$args   = func_get_args();
		$args[] = $this->get('_extra', array());
		Script::make(Arr::sort($args), strtolower(APP_MODULE).'/'.THEME_DIR);
	}
	
	public function asset($file, $isTheme = TRUE) {
		if ($isTheme) {
			$file = strtolower(APP_MODULE).THEME_DIR.ltrim($file, '/');
		}
		echo Url::to($file);
	}
}