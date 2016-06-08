<?php
namespace Domain\Form\{module};

use Zodream\Domain\Form;
use Zodream\Infrastructure\Request;
use Domain\Model\{module}\{model};
class {form} extends Form {
	protected $rules = array(
{data}
	);
	
	public function save($id = null) {
		$result = parent::save($id);
		if (!$result) {
			return false;
		}
		$model = new {model}();
		return $model->fill($this->get());
	}
}