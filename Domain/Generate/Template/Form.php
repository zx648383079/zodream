<?php
namespace Domain\Form;

use Zodream\Domain\Form;
use Zodream\Infrastructure\Request;
use Domain\Model\{model};
class {form} extends Form {
	public function get($id) {
		$model = new {model}();
		$this->send('data', $model->findById($id));
	}
	
	public function set() {
		if (!Request::getInstance()->isPost()) {
			return ;
		}
		$data = Request::getInstance()->post('{colums}');
		if (!$this->validata($data, array(
{data}
		))) {
			$this->send($data);
			return;
		}
		$model = new {model}();
		$model->add($data);
	}
}