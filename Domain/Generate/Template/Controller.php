<?php
namespace {modules}\Head;

use {modules}\Body\{model};

class {controller} extends Controller {
	protected $rules = array(
			'*' => '@'
	);
	
	function index{action}() {
		$model = new {model}();
		$data  = $model->findPage();
		$this->show($data);
	}
	
	function add{action}() {
		$model = new {model}();
		$data  = $model->updateById($id );
		$this->show();
	}
	
	function edit{action}($id) {
		$model = new {model}();
		$data  = $model->updateById($id);
		$this->show();
	}
	
	function delete{action}($id) {
		$model = new {model}();
		$data  = $model->deleteById($id);
		$this->ajaxJson(array(
				'status' => $data
		));
	}
	
	function view{action}($id = 0) {
		$model = new {model}();
		$data  = $model->findById($id);
		$this->show($data);
	}
}