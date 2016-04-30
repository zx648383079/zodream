<?php
namespace Zodream\Domain\Html;

use Zodream\Infrastructure\Request;
class Page {
	private $_total = 0;

	private $_index = 1;
	
	private $_pageSize = 20;
	
	private $_data = array();

	public function __construct($total, $pageSize = 20, $key = 'page') {
		$this->setTotal($total);
		$this->_index = max(1, Request::get($key, 1));
		$this->_pageSize = $pageSize;
	}

	/**
	 * 获取总共的数据
	 * @return int
	 */
	public function getTotal() {
		return $this->_total;
	}

	/**
	 * 设置总共的数据
	 * @param $total
	 */
	public function setTotal($total) {
		$this->_total = $total;
	}

	/**
	 * 获取一页的数据
	 * @return array
	 */
	public function getPage() {
		return $this->_data;
	}

	/**
	 * 设置一页的数据
	 * @param $data
	 */
	public function setPage($data) {
		$this->_data = $data;
	}

	/**
	 * 获取一页数据的长度
	 * @return int
	 */
	public function getPageCount() {
		return count($this->_data);
	}

	/**
	 * 获取查询分页的值
	 * @return mixed
	 */
	public function getLimit() {
		return max(($this->_index- 1) * $this->_pageSize, 0) . ','.$this->_pageSize;
	}

	/**
	 * 显示分页链接
	 * @param array $option
	 * @throws \Exception
	 */
	public function pageLink($option = array()) {
		echo $this->getLink($option);
	}

	/**
	 * 获取分页链接
	 * @param array $option
	 * @return string
	 * @throws \Exception
	 */
	public function getLink($option = array()) {
		$option['total'] = $this->_total;
		$option['pageSize'] = $this->_pageSize;
		$option['index'] = $this->_index;
		return PageLink::show($option);
	}
}