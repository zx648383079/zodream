<?php
namespace Zodream\Domain\Controller;
/**
 * 控制器基类
 *
 * @author Jason
 * @time 2015-12-19
 */
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Loader;
use Zodream\Infrastructure\Traits\LoaderTrait;

abstract class Controller extends BaseController {
	
	use LoaderTrait;
	
	function __construct($loader = null) {
		$this->loader = $loader instanceof Loader ? $loader : new Loader();
		if (is_bool($this->canCache)) {
			$this->canCache = Config::getValue('cache.auto', false);
		}
		if (is_bool($this->canCSRFValidate)) {
			$this->canCSRFValidate = Config::getValue('safe.csrf', false);
		}
	}
}