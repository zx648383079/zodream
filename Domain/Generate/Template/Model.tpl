<?php
namespace Domain\Model\{module};

use Domain\Model\Model;
/**
* Class {model}
{property}
*/
class {model} extends Model {
	public static $table = '{table}';

	protected $primaryKey = {pk};

	protected function rules() {
		return {data};
	}

	protected function labels() {
		return {labels};
	}
}