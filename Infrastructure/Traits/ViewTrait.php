<?php
namespace Zodream\Infrastructure\Traits;

use Zodream\Domain\Response\AjaxResponse;
use Zodream\Domain\Response\BaseResponse;
use Zodream\Domain\Response\HtmlResponse;
use Zodream\Domain\Response\RedirectResponse;
use Zodream\Domain\Routing\Url;
use Zodream\Infrastructure\Factory;

trait ViewTrait {
	/**
	 * 传递数据
	 *
	 * @param string|array $key 要传的数组或关键字
	 * @param string $value  要传的值
	 * @return static
	 */
	public function send($key, $value = null) {
		Factory::view()->set($key, $value);
		return $this;
	}
	
	/**
	 * 加载视图
	 *
	 * @param string $name 视图的文件名
	 * @param array $data 要传的数据
	 * @return BaseResponse
	 */
	public function show($name, $data = null) {
		if (!empty($data)) {
			$this->send($data);
		}
		if (strpos($name, '/') !== 0) {
			$pattern = 'Service.'.APP_MODULE.'.(.+)'.APP_CONTROLLER;
			$name = preg_replace('/^'.$pattern.'$/', '$1', get_called_class()).'/'.$name;
		}
		return new HtmlResponse(Factory::view()->setPath($name)->render());
	}
	
	public function ajax($data, $type = AjaxResponse::JSON) {
		return new AjaxResponse($data, $type);
	}
	
	public function redirect($url, $time = 0, $message = null, $status = 200) {
		return new RedirectResponse($url, $time, $message, $status);
	}
	
	public function goHome() {
		return $this->redirect(Url::getRoot());
	}
	
	public function goBack() {
		return $this->redirect(Url::referrer());
	}
}