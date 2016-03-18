<?php
namespace Zodream\Domain\Html;

class Page {
	private $_total = 0;
	
	private $_data = array();

	/**
	 * @var PageLink
	 */
	private $_pageLink;
	
	public function __construct($total = null) {
		if ($total instanceof PageLink) {
			$this->_pageLink = $total;
			$total = $this->_pageLink->getTotal();
		}
		$this->setTotal($total);
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
	 * @param int $pageSize
	 * @return mixed
	 */
	public function getLimit($pageSize = 20) {
		if (empty($this->_pageLink)) {
			$this->_pageLink = new PageLink();
			$this->_pageLink->init($this->getTotal(), $pageSize);
		}
		return $this->_pageLink->getLimit();
	}

	/**
	 * 显示分页链接
	 */
	public function pageLink() {
		echo $this->_pageLink->show();
	}
}