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
			$this->send('error', '验证失败！')
			return;
		}
		$model = new {model}();
		$result = $model->add($data);
		if (empty($result)) {
			$this->send($data);
			$this->send('error', '服务器出错了！')
			return;
		}
	}
}