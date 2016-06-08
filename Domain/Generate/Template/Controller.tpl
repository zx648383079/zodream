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
	
	function add{action}($id = null) {
		if (!empty()) {
			$model = new {model}();
			$data  = $model->findById($id);
			$this->send(['data', $data]);
		}
		$this->show();
	}

	/**
	* @param Post $post
	*/
	function addPost($post) {
		$form = new {form}();
		$form->set($post->get());
		if (empty($result)) {
			return;
		}
		Redirect::to(['{name}']);
	}
	
	function delete{action}($id) {
		$this->delete(new {model}(), $id);
	}
	
	function view{action}($id) {
		$model = new {model}();
		$data  = $model->findById($id);
		$this->show([
			'data' => $data
		]);
	}
}