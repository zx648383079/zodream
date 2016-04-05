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
	
	protected $_default;
	
	protected function __construct() {
		$this->reset();
	}

	/**
	 * 重新加载配置
	 */
	public function reset() {
		$config = Config::getInstance()->get('route');
		$this->_driver = $config['driver'];
		$this->_default = isset($config['default']) ? $config['default'] : 'home@index';
		unset($config['driver'], $config['default']);
		$this->set($config);
	}

	/**
	 * 获取默认路由
	 * @return string
	 */
	public function getDefault() {
		return $this->_default;
	}
	
	/**
	 * 获取驱动
	 * @return string
	 */
	public function getDriver() {
		if (empty($this->_driver)) {
			$this->_driver = 'Zodream\\Domain\\Routing\\Common';
		}
		return $this->_driver;
	}
}