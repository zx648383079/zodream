<?php
namespace Domain\Model;

use Zodream\Domain\Model;
class {model} extends Model {
	protected $table = '{name}';
	
	protected $fillable = array(
{data}
	);
	
	public function findPage($search, $start, $count) {
		return $this->find(array(
				'where' => " like '%{$search}%'",
				'limit' => $start.','.$count,
				'order' => ' desc' 
		));
	}
}