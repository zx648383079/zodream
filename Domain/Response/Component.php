<?php
namespace Zodream\Domain\Response;

class Component extends View {
	protected $components;
	
	/**
	 * 按部件加载视图
	 * @param string $name
	 * @param string $data
	 * @return self
	 */
	public function view($name = 'index', $data = null) {
		extract($data);
		ob_start();
		include($this->getView($name));
		$this->components .= ob_get_contents();
		ob_end_clean();
		return $this;
	}
	
	/**
	 * 结束并释放视图
	 */
	public function render() {
		ResponseResult::make($this->components);
	}
}