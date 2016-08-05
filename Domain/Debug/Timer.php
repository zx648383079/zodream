<?php
namespace Zodream\Domain\Debug;

use Zodream\Infrastructure\ObjectExpand\TimeExpand;
class Timer {
	protected $startTime;
	
	public function begin() {
        $this->startTime = TimeExpand::millisecond();
	}
	
	public function end() {
		return TimeExpand::millisecond() - $this->startTime;
	}
}