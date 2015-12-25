<?php
namespace Zodream\Domain\Response;

use Zodream\Infrastructure\Config;
use Zodream\Domain\Routing\UrlGenerator;
use Zodream\Domain\Html\Script;

defined('THEME_DIR') or define('THEME_DIR', '/' .Config::theme('dir').'/');

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
		echo UrlGenerator::to($file);
	}
}