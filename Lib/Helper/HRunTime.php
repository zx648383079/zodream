<?php
namespace App\Lib\Helper;

class HRunTime implements IBase
{
	private $startTime = 0;
	private $stopTime = 0;

	public function __construct() {
		
	}

	private function get_microtime()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}

	public function start()
	{
		$this->startTime = $this->get_microtime();
	}

	public function stop()
	{
		$this->stopTime = $this->get_microtime();
	}

	public function spent()
	{
		if(!isset($this->request->get['route']))
			$this->request->get['route']='common/home';
		return $this->request->get['route'].' | '.round(($this->stopTime - $this->startTime) * 1000, 1).'ms,';
	}

}