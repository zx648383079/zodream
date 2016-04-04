<?php
namespace Zodream\Domain;

use Zodream\Domain\Filter\DataFilter;
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\Traits\AjaxTrait;
use Zodream\Infrastructure\Traits\ViewTrait;
abstract class Form extends MagicObject{

	protected $rules = array();

	protected $status = array(
		'操作成功完成！',
		'验证失败！',
		'服务器错误！',
		'其他错误！'
	);

	use ViewTrait, AjaxTrait;

	/**
	 * 填充表单数据
	 * @param array $data
	 */
	public function __construct($data = array()) {
		$fill = implode(',', array_keys($this->rules));
		$this->set(empty($data) ? Request::post($fill) : ArrayExpand::getValues($fill, $data));
	}

	/**
	 * 验证POST数据
	 * @param array $request
	 * @param array $args
	 * @return bool
	 */
	public function validate($request = array(), $args = array()) {
		if (empty($request) || empty($args)) {
			$request = $this->get();
			$args = $this->rules;
		}
		return DataFilter::validate($request, $args);
	}

	public function save($id = null) {
		if (!is_null($id)) {
			$this->set('id', intval($id));
		}
	}

	/**
	 * 执行方法
	 * @param string $action
	 * @return bool|mixed
	 */
	public function runAction($action) {
		if (!Request::isPost()) {
			return false;
		}
		if (!method_exists($this, $action)) {
			return false;
		}
		$args = func_get_args();
		unset($args[0]);
		return call_user_func_array(array($this, $action), $args);
	}

	public function sendMessage($status = 0) {
		$this->send('message', isset($this->status[$status]) ? $this->status[$status] : '未知错误！');
	}
}