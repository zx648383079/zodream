<?php
namespace Zodream\Infrastructure\Traits;

use Zodream\Domain\Response\Component;
trait ComponentTrait {
	protected function component($name = 'index', $data = null) {
		return Component::getInstance()->view($name, $data);
	}
	
	protected function render() {
		return Component::getInstance()->render();
	}
}