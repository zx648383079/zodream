<?php
namespace Service\{module};

use Domain\Model\{model};
use Domain\Form\{form};
class {controller} extends Controller {
	protected $rules = array(
			'*' => '@'
	);
	
	function index{action}() {
		$model = new {model}();
		$this->show(array(
			'title' => '',
			'page' => $model->findPage()
		));
	}
	
	function add{action}() {
		$form = new {form}();
		$form->set();
		$this->show();
	}
	
	function edit{action}($id) {
		$form = new {form}();
		$form->set();
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