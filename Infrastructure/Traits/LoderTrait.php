<?php
namespace Zodream\Infrastructure\Traits;

trait LoaderTrait {
	protected $loader;
	
	public function __get($key) {
		return $this->loader->get($key);
	}
	
	public function __set($key, $value) {
		$this->loader->set($key, $value);
	}
}