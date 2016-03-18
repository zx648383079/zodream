<?php
namespace Domain\Model\{module};

use Zodream\Domain\Model;
use Zodream\Domain\Html\Page;
class {model} extends Model {
	protected $table = '{table}';
	
	protected $fillAble = array(
{data}
	);
	
	public function findPage() {
		$page = new Page($this->count());
		$page->setPage($this->find(array(
				'order' => 'create_at desc',
				'limit' => $page->getLimit()
			)
		));
		return $page;
	}
}