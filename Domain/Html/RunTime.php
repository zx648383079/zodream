<?php
namespace Zodream\Body\Html;
/**
 * 运行时间纪录
 *
 * @author Jason
 * @time 2015-12-1
 */
class RunTime {
	private $startTime = 0;
	private $stopTime = 0;
	
	public function __construct() {
		$this->start();
	}
	
	private function get_microtime() {
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}
	
	public function start() {
		$this->startTime = $this->get_microtime();
	}
	
	public function stop() {
		$this->stopTime = $this->get_microtime();
	}
	
	public function spent() {
		if(!isset($this->request->get['route']))
			$this->request->get['route']='common/home';
		return $this->request->get['route'].' | '.round(($this->stopTime - $this->startTime) * 1000, 1).'ms,';
	}
}