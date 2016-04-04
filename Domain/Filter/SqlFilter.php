<?php
namespace Zodream\Domain\Filter;
/**
 * 数据库语句过滤
 *
 * @author Jason
 */
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;

class SqlFilter {
	/********
	 SQL中的关键字数组
	 *********/
	private $sqlKeys = array (
			/*'show', 'alter', 'drop', 'create,'*/
			'select', 'update', 'set', 'delete', 'insert',
			'from', 'values', 'left', 'right', 'inner', 'exec',
			'where', 'and', 'or', 'group', 'having', 'order',
			'asc', 'desc', 'limit'
	);
	
	private $prefix  = NULL;
	/**
	 * 公有构造函数
	 *
	 * @access public
	 *
	 * @param string $prefix 前缀.
	 *
	 */
	public function __construct($prefix = NULL) {
		$this->prefix = $prefix;
	}

	/**
	 * 根据数组获取SQL语句
	 *
	 * @access public
	 *
	 * @param array $param 要操作的数组.
	 * @param boolean $sort 是否先进行排序.
	 * @return array 返回排序的数组,
	 */
	public function getSQL($param, $sort = FALSE) {
		if ($sort) {
			$param = ArrayExpand::sortByKey($param, $this->sqlKeys);
		}
		return $this->sqlCheck($param);
	}
	
	protected $select = array();
	
	protected $table = array();
	
	protected $order = array();
	
	protected $group = array();
	
	protected $limit = null;
	
	protected $offset = null;
	
	public function create($table) {
		
	}
	
	public function select($field = '*') {
		if (!is_array($field)) {
			$field = func_get_args();
		}
		foreach ($field as $key => $value) {
			if (is_int($key)) {
				$this->select[] = $value;
			} else {
				$this->select[] = $value. ' AS '.$key;
			}
		}
		return $this;
	}
	
	public function count($column = '*') {
		return $this->_selectFunction(__FUNCTION__, $column);
	}
	
	public function max($column)  {
		return $this->_selectFunction(__FUNCTION__, $column);
	}
	
	public function min($column)  {
		return $this->_selectFunction(__FUNCTION__, $column);
	}
	
	public function avg($column)  {
		return $this->_selectFunction(__FUNCTION__, $column);
	}
	
	public function sum($column)  {
		return $this->_selectFunction(__FUNCTION__, $column);
	}
	
	private function _selectFunction($name, $column) {
		$this->select[] = "{$name}({$column}) AS {$name}";
		return $this;
	}
	
	public function from($table) {
		if (!is_array($table)) {
			$table = func_get_args();
		}
		foreach ($table as $key => $value) {
			if (is_int($key)) {
				$this->table[] = $value;
			} else {
				$this->table[] = $value. ' '.$key;
			}
		}
		return $this;
	}
	
	public function where() {
		
	}
	
	public function join($table) {
		
	}
	
	public function group($column) {
		if (is_string($column)) {
			$column = func_get_args();
		}
		if (is_array($column)) {
			$this->group = array_merge($this->group, $column);
		}
		return $this;
	}
	
	/**
	 * 用条件筛选已分组的组
	 */
	public function having() {
		
	}
	
	public function oreder($name, $sort = 'ASC') {
		if (is_string($name)) {
			$this->order[] = $name.' '.strtoupper($sort);
		} elseif (is_array($name)) {
			foreach ($name as $key => $value) {
				if (is_int($key)) {
					$this->order[] = $value;
				} else {
					$this->order[] = $key.' '. strtoupper($value);
				}
			}
		}
		return $this;
	}

	/**
	 * 当长度为null是，把第一个参数作为长度
	 * @param int $start
	 * @param int $length
	 * @return $this
	 */
	public function limit($start, $length = null) {
		$this->limit = $start;
		if (null !== $length) {
			$this->limit .= ','.$length;
		}
		return $this;
	}
	
	public function offset($start) {
		$this->offset = $start;
		return $this;
	}
	
	/**
	 * 根据SQL关键字拼接语句
	 *
	 * @access private
	 *
	 * @param string $key 关键字.
	 * @param string|array $value 值.
	 * @return string 返回拼接后的SQL语句,
	 */
	private function sqlJoin($key, $value) {
		if (empty($value)) {
			return null;
		}
		$result = ' ';
		switch ($key) {
			/*case 'show':
			 $result.='SHOW '.$this->sqlCheck($value);
			 break;
				case 'create':
				$result.='CREATE TABLE '.$this->sqlCheck($value,',');
				break;
				case 'alter':
				$result.='ALTER TABLE '.sqlCheck($value,',');
				break;
				case 'drop':
				$result.='DROP TABLE '.sqlCheck($value,',');
				break;*/
			case 'exec':
				$result .= 'EXEC '.$this->sqlCheck($value);
				break;
			case 'select':
				$result .= 'SELECT '.$this->sqlCheck($value, ',');
				break;
			case 'from':
				$result .= 'FROM '.$this->sqlCheck($value, ',', $this->prefix);
				break;
			case 'update':
				$result .= 'UPDATE '.$this->sqlCheck($value, ',');
				break;
			case 'set':
				$result .= 'SET '.$this->sqlCheck($value, ',');
				break;
			case 'delete':
				$result .= 'DELETE FROM '.$this->sqlCheck($value, ',');
				break;
			case 'insert':
				$result .= 'INSERT INTO '.$this->sqlCheck($value);
				break;
			case 'values':
				$result .= 'VALUES '.$this->sqlCheck($value, ',');
				break;
			case 'limit':
				$result .= 'LIMIT '.$this->sqlCheck($value, ',');
				break;
			case 'order':
				$result .= 'ORDER BY '.$this->sqlCheck($value, ',');
				break;
			case 'group':
				$result .= 'GROUP BY '.$this->sqlCheck($value, ',');
				break;
			case 'having':
				$result .= 'HAVING '.$this->sqlCheck($value);
				break;
			case 'where':
				$result .= 'WHERE '.$this->sqlCheck($value, ' AND ');
				break;
			case 'or':
				$result .= 'OR '.$this->sqlCheck($value);
				break;
			case 'and':
				$result .= 'AND '.$this->sqlCheck($value);
				break;
			case 'desc':
				$result .= $this->sqlCheck($value, ',').' DESC';
				break;
			case 'asc':
				$result .= $this->sqlCheck($value, ',').' ASC';
				break;
			default:															//默认为是这些关键词 'left','right','inner'
				$result .= strtoupper($key).' JOIN '.$this->sqlCheck($value, ' ON ', $this->prefix);
				break;
		}
	
		return $result;
	}

	/**
	 * SQL关键字检测
	 *
	 * @access private
	 *
	 * @param string|array $value 要检查的语句或数组.
	 * @param string $link 数组之间的连接符.
	 * @param string $pre
	 * @param string $end
	 * @return string 返回拼接的语句,
	 */
	private function sqlCheck($value, $link = ' ', $pre = null, $end = null) {
		$result = '';
		if (is_array($value)) {
			foreach ($value as $key => $v) {
				$space = ' ';
				//把关键字转换成小写进行检测
				$low    = strtolower($key);
				$lowkey = str_replace('`', '', $low);                   //解决重关键字冲突关键
				if (in_array($lowkey, $this->sqlKeys, TRUE)) {
					$space .= $this->sqlJoin($lowkey, $v);
				} else {
					if (is_numeric($key)) {
						if (empty($result)) {
							$space .= $this->sqlCheck($v, ' ', $pre, $end);
						} else {
							$space .= $link. $this->sqlCheck($v);
						}
					} else {
						$space .= $pre. $key. $end. $link. $this->sqlCheck($v);
					}
				}
	
				$result .= $space;
			}
				
		} else {
			$unsafe = $this->sqlKeys;
			array_push($unsafe, ';');                        //替换SQL关键字和其他非法字符，
			$safe = $this->safeCheck($value, '\'', $unsafe, ' ');
			$safe = $this->safeCheck($value, '"', $unsafe, ' ');
			if (strpos($safe, '(') !== FALSE) {                      //验证是表名还是其他
				$result .= $safe;
			} else {
				if (!empty($pre) && strpos($safe, $pre) === 0) {            //判断是否存在重复前缀；
					$pre = '';
				}
				$result .= $pre. $safe. $end;
			}
		}
	
		$result = preg_replace('/\s+/', ' ', $result);
		$result = str_replace("WHERE AND", "WHERE", $result);
		$result = str_replace("WHERE OR", "WHERE", $result);
	
		return $result;
	}
	
	/**
	 * 检查是否是字符串语句
	 *
	 * @access private
	 *
	 * @param string $unsafe 要检查的语句.
	 * @param string $scope 排除语句的标志.
	 * @param string|array $find 要查找的关键字.
	 * @param string|array $enresplace 替换的字符或数组.
	 * @return string 返回完成检查的语句,
	 */
	private function safeCheck($unsafe, $scope, $find, $enresplace) {
		$safe = '';
		$arr  = explode($scope, $unsafe);
		$len  = count($arr);
		if ($len == 1) {
			$safe = $unsafe;
		} else {
			foreach ($arr as $key => $val) {
				if ($key % 2 == 0) {
					$low   = strtolower($val);                      //转化为小写
					$safe .= str_replace($find, $enresplace, $low);
				} else {
					//如果排除标志不是成对出现，默认在最后加上
					$safe .= $scope. $val. $scope;
				}
			}
		}
	
		return $safe;
	}
}