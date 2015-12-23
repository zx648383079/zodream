<?php
namespace Zodream\Infrastructure\Traits;

use Zodream\Infrastructure\Response\Component;
trait ComponentTrait {
	protected function component($name = 'index', $data = null) {
		return Component::view($name, $data);
	}
	
	protected function render() {
		return Component::render();
	}
}