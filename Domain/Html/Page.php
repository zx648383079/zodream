<?php
namespace Zodream\Domain\Html;

class Page {
	private $_total = 0;
	
	private $_data = array();
	
	private $_pageLink;
	
	public function __construct($total = null) {
		if ($total instanceof PageLink) {
			$this->_pageLink = $total;
			$total = $this->_pageLink->getTotal();
		}
		$this->setTotal($total);
	}
	
	public function getTotal() {
		return $this->_total;
	}
	
	public function setTotal($total) {
		$this->_total = $total;
	}
	
	public function getPage() {
		return $this->_data;
	}
	
	public function setPage($data) {
		$this->_data = $data;
	}
	
	public function getPageCount() {
		return count($this->_data);
	}
	
	public function getLimit($pageSize = 20) {
		if (empty($this->_pageLink)) {
			$this->_pageLink = new PageLink();
			$this->_pageLink->init($this->getTotal(), $pageSize);
		}
		return $this->_pageLink->getLimit();
	}
	
	public function pageLink() {
		echo $this->_pageLink->show();
	}
}