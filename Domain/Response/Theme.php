<?php
namespace Zodream\Domain\Response;

use Zodream\Infrastructure\Config;
use Zodream\Domain\Routing\UrlGenerator;
use Zodream\Domain\Html\Script;

defined('THEME_DIR') or define('THEME_DIR', '/' .Config::theme('dir').'/');

class Theme extends View {
	
	protected function __construct() {
		$this->setAsset('UserInterface/'.APP_MODULE.'/'.THEME_DIR);
	}
	
	function find() {
	
	}
	
	/**
	 * 输出脚本
	 */
	public function jcs() {
		$args   = func_get_args();
		$args[] = $this->get('_extra', array());
		Script::make(Arr::sort($args), $this->getAsset());
	}
	
	public function asset($file, $isTheme = TRUE) {
		if ($isTheme) {
			$file = $this->getAsset().ltrim($file, '/');
		}
		echo UrlGenerator::to($file);
	}
}