<?php
namespace Domain\Form\{module};

use Zodream\Domain\Form;
use Zodream\Infrastructure\Request;
use Domain\Model\{module}\{model};
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
		if (!$this->validate($data, array(
{data}
		))) {
			$this->send($data);
			return;
		}
		$model = new {model}();
		$model->add($data);
	}
}