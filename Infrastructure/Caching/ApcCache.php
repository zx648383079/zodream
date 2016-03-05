<?php 
namespace Zodream\Infrastructure\Caching;
/**
* 缓存类
* 
* @author Jason
*/

class ApcCache extends Cache {
	
	public $useApcu = false;
	
	public function __construct() {
		$extension = $this->useApcu ? 'apcu' : 'apc';
		if (!extension_loaded($extension)) {
			//throw new \er("ApcCache requires PHP $extension extension to be loaded.");
		}
	}
	
	protected function getValue($key) {
		return $this->useApcu ? apcu_fetch($key) : apc_fetch($key);
	}
	
	protected function setValue($key, $value, $duration) {
		$this->useApcu ? apcu_store($key, $value, $duration) : apc_store($key, $value, $duration);
	}
	
	protected function addValue($key, $value, $duration) {
		return $this->useApcu ? apcu_add($key, $value, $duration) : apc_add($key, $value, $duration);
	}
	
	protected function hasValue($key) {
		return $this->useApcu ? apcu_exists($key) : apc_exists($key);
	}
	
	protected function deleteValue($key) {
		return $this->useApcu ? apcu_delete($key) : apc_delete($key);
	}
	
	protected function clearValue() {
		return $this->useApcu ? apcu_clear_cache() : apc_clear_cache('user');
	}
}