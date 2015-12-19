<?php
namespace Zodream\Domain\Routing;
/**
 * 路由的配置信息
 */

use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\Traits\SingletonPattern;
use Zodream\Infrastructure\Config;

class RouteConfig extends MagicObject {
	
	use SingletonPattern;
	
	protected $_driver;
	
	protected function __construct() {
		$this->reset();
	}
	
	public function reset() {
		$config = Config::getInstance()->get('route');
		$this->_driver = $config['driver'];
		unset($config['driver']);
		$this->set($config);
	}
	
	/**
	 * 获取驱动
	 * @throws Error
	 */
	public function getDriver() {
		if (empty($this->_driver)) {
			$this->_driver = 'Zodream\\Domain\\Routing\\Common';
		}
		return $this->_driver;
	}
}