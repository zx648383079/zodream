<?php
namespace Zodream\Domain;
/**
 * 自动加载功能
 *
 * @author Jason
 */
use Zodream\Infrastructure\Error\Error;
use Zodream\Service\Config;
use Zodream\Infrastructure\Traits\SingletonPattern;
use Zodream\Infrastructure\Base\MagicObject;

class Autoload extends MagicObject {
	
	use SingletonPattern;
	
	protected $_registerAlias = false;
	
	private function __construct() {
		$this->set(Config::alias([]));
	}
	/**
	 * 注册别名
	 */
	public function registerAlias() {
		if (!$this->_registerAlias) {
			spl_autoload_register(array($this, '_load'), true, true);
			$this->_registerAlias = TRUE;
		}
		return $this;
	}

	/**
	 * 设置别名
	 * @param string $alias
	 * @return bool
	 */
	protected function _load($alias) {
		if (!class_exists($alias)) {
			$alias = end(explode('\\', $alias));
			if ($this->has($alias)) {
				return class_alias($this->get($alias), $alias);
			}
		}
		return false;
	}

	/**
	 * 自定义错误输出
	 * @return $this
	 */
	public function bindError() {
	    $error = new Error();
	    $error->bootstrap();
		return $this;
	}
	
	private function __clone() {
		
	}
}