<?php
namespace Zodream\Domain\Html;
/**
 * 分页类
 * 使用方式:
 * $page = new Page();
 * $page->init(1000, 20);
 * $page->setNotActiveTemplate('<span>&nbsp;{a}&nbsp;</span>');
 * $page->setActiveTemplate('{a}');
 * echo $page->show();
 */
use Zodream\Infrastructure\Request;
use Zodream\Domain\Routing\UrlGenerator;

class PageLink {
	/**
	 * 总条数
	 */
	private $_total;
	/**
	 * 每页大小
	 */
	private $_pageSize;
	/**
	 * 总页数
	 */
	private $_pageNum;
	/**
	 * 当前页
	 */
	private $_page;
	/**
	 * 分页变量
	 */
	private $_pageParam;
	/**
	 * LIMIT XX,XX
	 */
	private $_limit;
	/**
	 * 数字分页显示
	 */
	private $_listNum = 8;
	/**
	 * 分页显示模板
	 * 可用变量参数
	 * {total}      总数据条数
	 * {pagesize}   每页显示条数
	 * {start}      本页开始条数
	 * {end}        本页结束条数
	 * {pagenum}    共有多少页
	 * {pre}        上一页
	 * {next}       下一页
	 * {list}       数字分页
	 * {goto}       跳转按钮
	 */
	private $_template = '<nav><ul class="pagination pagination-lg">{pre}{list}{next}</ul></nav>'; //'<div><span>共有{total}条数据</span><span>每页显示{pagesize}条数据</span>,<span>本页{start}-{end}条数据</span><span>共有{pagenum}页</span><ul>{pre}{list}{next}{goto}</ul></div>';
	/**
	 * 当前选中的分页链接模板
	 */
	private $_activeTemplate = '<li class="active"><span>{text}</span></li>';//'<li class="active"><a href="javascript:;">{text}</a></li>';
	/**
	 * 未选中的分页链接模板
	 */
	private $_notActiveTemplate = '<li><a href="{url}">{text}</a></li>';//'<li><a href="{url}">{text}</a></li>';
	/**
	 * 显示文本设置
	 */
	private $_config = array('pre' => '《', 'next' => '》');
	
	public function __construct($total = null, $pageSize = 10, $pageParam = 'page') {
		if (!empty($total)) {
			$this->init($total, $pageSize, $pageParam);
			echo $this->show();
		}
	}
	
	/**
	 * 初始化
	 * @param int $total       总条数
	 * @param int $pageSize    每页大小
	 * @param string $pageParam   分页变量
	 */
	public function init($total, $pageSize = 10, $pageParam = 'page') {
		$this->setTotal($total);
		$this->_pageSize = intval($pageSize);
		$this->_pageParam = $pageParam;
		$this->_pageNum = ceil($this->_total / $this->_pageSize);
		$this->_setPage();
		$this->_setLimit();
	}
	
	public function getTotal() {
		return $this->_total;
	}
	
	public function setTotal($total) {
		$this->_total = intval($total);
	}
	 
	/**
	 * 设置分页模板
	 * @param string $template    模板配置
	 */
	public function setTemplate($template) {
		$this->_template = $template;
	}
	 
	/**
	 * 设置选中分页模板
	 * @param string $activeTemplate      模板配置
	 */
	public function setActiveTemplate($activeTemplate) {
		$this->_activeTemplate = $activeTemplate;
	}

	/**
	 * 设置未选中分页模板
	 * @param string $notActiveTemplate   模板配置
	 */
	public function setNotActiveTemplate($notActiveTemplate) {
		$this->_notActiveTemplate = $notActiveTemplate;
	}

	/**
	 * 返回分页
	 * @return string
	 */
	public function show() {
		return str_ireplace(array(
				'{total}',
				'{pagesize}',
				'{start}',
				'{end}',
				'{pagenum}',
				'{pre}',
				'{next}', 
				'{list}',
				'{goto}',
		), array(
				$this->_total,
				$this->_setPageSize(),
				$this->_star(),
				$this->_end(),
				$this->_pageNum,
				$this->_prev(),
				$this->_next(),
				$this->_pagelist(),
				$this->_gopage(),
		), $this->_template);
	}
	 
	/**
	 * 获取limit起始数
	 * @return int
	 */
	public function getOffset() {
		return ($this->_page - 1) * $this->_pageSize;
	}
	 
	/**
	 * 设置LIMIT
	 * @return string 
	 */
	private function _setLimit() {
		$this->_limit = ($this->_page - 1) * $this->_pageSize . ','.$this->_pageSize;
	}

	public function getLimit() {
		return $this->_limit;
	}

	/**
	 * 初始化当前页
	 * @return int
	 */
	private function _setPage() {
		$this->_page = Request::getInstance()->get($this->_pageParam, 1);
		if ($this->_page < 0) {
			$this->_page = 1;
		}
		if ($this->_page > $this->_pageNum) {
			$this->_page = $this->_pageNum;
		}
	}

	/**
	 * 本页开始条数
	 * @return int
	 */
	private function _star() {
		if ($this->_total == 0) {
			return 0;
		}
		return ($this->_page - 1) * $this->_pageSize + 1;
	}

	/**
	 * 本页结束条数
	 * @return int
	 */
	private function _end() {
		return min($this->_page * $this->_pageSize, $this->_total);
	}

	/**
	 * 设置当前页大小
	 * @return int
	 */
	private function _setPageSize() {
		return $this->_end() - $this->_star() + 1;
	}

	/**
	 * 上一页
	 * @return string
	 */
	private function _prev() {
		if ($this->_page > 1) {
			return $this->_replaceLine($this->_page - 1, $this->_config['pre']);
		}
		return null;
	}

	/**
	 * 分页数字列表
	 * @return string
	 */
	private function _pagelist() {
		if ($this->_pageNum < 2) {
			return null;
		}
		$linkPage = '';
		$linkPage .= $this->_replaceLine(1);
		$lastList= floor($this->_listNum / 2);
		$i = 0;
		$length = 0;
		if ($this->_pageNum < $this->_listNum || $this->_page - $lastList< 2 || $this->_pageNum - $this->_listNum < 2) {
			$i = 2;
			if ($this->_pageNum <= $this->_listNum) {
				$length = $this->_pageNum - 1;
			} else {
				$length = $this->_listNum;
			}
		} elseif ($this->_page - $lastList>= 2 && $this->_page + $lastList<= $this->_pageNum) {
			$i = $this->_page - $lastList;
			$length = $this->_page + $lastList- 1;
		} elseif ($this->_page + $lastList> $this->_pageNum) {
			$i = $this->_pageNum - $this->_listNum + 1;
			$length = $this->_pageNum - 1;
		}
		if ($this->_page > $lastList+ 1 && $i > 2) {
			$linkPage .= $this->_replace(null, '...');
		}
		for (; $i <= $length; $i ++) {
			$linkPage .= $this->_replaceLine($i);
		}
		if ($this->_page < $this->_pageNum - $lastList&& $length < $this->_pageNum - 1) {
			$linkPage .= $this->_replace(null, '...');
		}
		$linkPage .= $this->_replaceLine($this->_pageNum);
		return $linkPage;
	}

	/**
	 * 下一页
	 * @return string
	 */
	private function _next() {
		if ($this->_page < $this->_pageNum) {
			return $this->_replaceLine($this->_page + 1, $this->_config['next']);
		}
		return null;
	}

	/**
	 * 跳转按钮
	 * @return string
	 */
	private function _goPage() {
		return '&nbsp;<input type="text" value="' . $this->_page . '" onkeydown="javascript:if(event.keyCode==13){var page=(this.value>' . $this->_pageNum . ')?' . $this->_pageNum . ':this.value;location=\'' . UrlGenerator::to(null) . '&page=\'+page+\'\'}" style="width:25px;"/><input type="button" onclick="javascript:var page=(this.previousSibling.value>' . $this->_pageNum . ')?' . $this->_pageNum . ':this.previousSibling.value;location=\'' . UrlGenerator::to() . '&page=\'+page+\'\'" value="GO"/>';
	}
	
	private function _replaceLine($page, $text = null) {
		return $this->_replace(
				UrlGenerator::to(null, array(
					$this->_pageParam => $page
				)),
				$text == null ? $page : $text, 
				$page == $this->_page
		);
	}

	/**
	 * 模板替换
	 * @param string $replace     替换内容
	 * @param string $result      条件
	 * @return string
	 */
	private function _replace($url, $text, $result = TRUE) {
		$template = ($result ? $this->_activeTemplate : $this->_notActiveTemplate);
		$html = str_replace('{url}', $url, $template);
		return str_replace('{text}', $text, $html);
	}
}