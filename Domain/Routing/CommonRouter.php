<?php
namespace Zodream\Domain\Routing;
/**
 * 普通链接方式 即 z
 */
use Zodream\Infrastructure\DomainObject\Route;
use Zodream\Infrastructure\DomainObject\RouterObject;
use Zodream\Infrastructure\Request;

class CommonRouter extends BaseRouter implements RouterObject {
	
	protected $label = 'z';
	
	public function __construct($label = 'z') {
		$this->label = $label;
	}

	public function to($file) {
		 return [$this->label => $file];
	}

	/**
	 * @return ResponseObject
	 */
	public function run() {
		return $this->runByUri(Request::get($this->label))->run();
	}
}