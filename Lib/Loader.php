<?php
namespace App\Lib;

final class Loader {
	private $data = array();
	
	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : NULL);
	}
	
	public function set($key, $value) {
		if (is_string($value)) {
			$this->data[$key] = new $value;
		} else {
			$this->data[$key] = $value;
		}
	}
	
	public function __get($key) {
		return $this->get($key);
	}
	
	public function __set($key, $value) {
		$this->set($key, $value);
	}
	
	public function has($key) {
		return isset($this->data[$key]);
	}
	
	public function model($model) {
		$class = APP_MODULE.'/Model/'.ucfirst($model).'Model';
		if (class_exists($class)) {
			$this->set($model.'Model', new $class);
		} else {
			exit('Error: Could not load model ' . $model . '!');
		}
	}
	
	public function plugin($plugin) {
		$file = APP_DIR. '/Lib/Plugin/'. $plugin. '.php';
		if (file_exists($file)) {
			include_once($file);
		} else {
			exit('Error: Could not load plugin ' . $library . '!');
		}
	}
	
	public function library($library) {
		$class = APP_MODULE.'/Lib/'.ucfirst($library);
		if (class_exists($class)) {
			$this->set(str_replace('/', '_', $library), new $class);
		} else {
			exit('Error: Could not load library ' . $library . '!');
		}
	}
}