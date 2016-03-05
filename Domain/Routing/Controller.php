<?php
namespace Zodream\Domain\Routing;
/**
 * 控制器基类
 *
 * @author Jason
 * @time 2015-12-19
 */
use Zodream\Infrastructure\Loader;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Language;
use Zodream\Infrastructure\Traits\LoaderTrait;
use Zodream\Infrastructure\Traits\ViewTrait;

abstract class Controller extends BaseController {
	
	use LoaderTrait, ViewTrait;
	
	function __construct($loader = null) {
		$this->loader = $loader instanceof Loader ? $loader : new Loader();
	}
}