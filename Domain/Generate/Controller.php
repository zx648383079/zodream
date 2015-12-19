<?php
namespace Zodream\Hand\Generate;

class Controller {
private $_name;
	private $_column;
	
	public function __construct($name = 'Home', $column = array()) {
		$this->_name = ucfirst($name);
		$this->_column = $column;
	}
	
	private function _index() {
		$this->_action('index', 
				'$data = $model->findLimit();'
		);
	}
	
	private function _add() {
		$this->_action('add',
				'$data = $model->fill();'
		);
	}
	
	private function _edit() {
		$this->_action('edit',
				'$data = $model->update();'
		);
	}
	
	private function _delete() {
		$this->_action('delete',
				'$data = $model->deleteById();'
		);
	}
	
	private function _view() {
		$this->_action('view',
				'$data = $model->findById();'
		);
	}
}