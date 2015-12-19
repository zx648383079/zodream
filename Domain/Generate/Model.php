<?php
namespace Zodream\Hand\Generate;

class Model {
	private $_name;
	private $_column;
	
	public function __construct($name = 'Home', $column = array()) {
		$this->_name = ucfirst($name);
		$this->_column = $column;
	}
	
	private function _fill() {
		$data = '';
		foreach ($this->_column as $value) {
			if ($value['Extra'] === 'auto_increment') {
				continue;
			}
			$data .= $value['Field']. ',';
		}
		$data = rtrim($data, ',');
	}
}