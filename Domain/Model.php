<?php 
namespace Zodream\Domain;
/**
* 数据基类
* 
* @author Jason
*/
use Zodream\Domain\Filter\SqlFilter;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

abstract class Model {
	/**
	 * @var \Zodream\infrastructure\Database\Database
	 */
	protected $db;

	protected $table;

	protected $fillAble = array();
	
	protected $prefix;

	protected $sequence = array('join', 'left', 'inner', 'right', 'where', 'group', 'having', 'order', 'limit', 'offset');
	
	public function __construct() {
		$configs = Config::getInstance()->get('db');
		$this->db = call_user_func(array($configs['driver'], 'getInstance'), $configs);
		$this->prefix = $configs['prefix'];
		if (isset($this->table)) {
			$this->setTable($this->table);
		}
	}

	protected function addPrefix($table) {
		return $this->prefix. StringExpand::firstReplace($table, $this->prefix, null);
	}

	public function setTable($table) {
		$this->table = $this->addPrefix($table);
		return $this;
	}

	public function changedDatabase($database) {
		$this->db->execute('use '.$database);
		return $this;
	}

	/**
	 * 填充数据 自动识别添加或修改
	 * 添加 有一个数组参数 或 多个 非数组参数（与fillAble字段对应）
	 * 修改 有两个参数 第一个为数组 第二个为条件,如果第二个参数是数字，则为id
	 * 关联数组参数不需要一一对应，自东根据 fillAble 取需要的
	 * @return int
	 */
	public function fill() {
		if (func_num_args() === 0) {
			return false;
		}
		$args = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();
		$data = ArrayExpand::combine($this->fillAble, $args, false);
		if (func_num_args() == 1 || !is_array(func_get_arg(0))) {
			return $this->add($data);
		}
		$param = func_get_arg(1);
		if (is_numeric($param)) {
			$param = 'id = '.$param;
		}
		return $this->updateValues($data, $param);
	}
	
	/**
	 * 新增记录
	 *
	 * @access public
	 *
	 * @param array $addData 需要添加的集合
	 * @return int 返回最后插入的ID,
	 */
	public function add(array $addData) {
		$addFields = implode('`,`', array_keys($addData));
		return $this->insert("`{$addFields}`", StringExpand::repeat('?', count($addData)), array_values($addData));
	}
	 
	/**
	 * 修改记录
	 *
	 * @param array|string $param 条件 默认使用AND 连接
	 * @param array $updateData 需要修改的内容
	 * @return int 返回影响的行数,
	 */
	public function updateValues(array $updateData, $param) {
		$setData = '';
		foreach ($updateData as $key => $value) {
			$setData .= "`$key` = ?,";
		}
		$setData = substr($setData, 0, -1);
		return $this->update($setData, $this->getCondition($param), array_values($updateData));
	}

	/**
	 * 更具id 修改记录
	 * @param string|integer $id
	 * @param array $data
	 * @return int
	 */
	public function updateById($id, array $data) {
		return $this->updateValues($data, 'id = '.intval($id));
	}
	 
	/**
	 * 设置bool值
	 *
	 * @param string $filed
	 * @param string $where
	 * @return int
	 */
	public function updateBool($filed, $where) {
		return $this->update("{$filed} = CASE WHEN {$filed} = 1 THEN 0 ELSE 1 END", $this->getCondition($where));
	}
	
	/**
	 * int加减
	 *
	 * @param string|string $filed
	 * @param string $where
	 * @param integer $num
	 * @return int
	 */
	public function updateOne($filed, $where, $num = 1) {
		$sql[] = array();
		foreach ((array)$filed as $key => $item) {
			if (is_numeric($key)) {
				$sql[] = "`$item` = `$item` ".$this->getNumber($num);
			} else {
				$sql[] = "`$key` = `$key` ".$item;
			}
		}
		return $this->update(implode(',', $sql), $this->getCondition($where));
	}

	/**
	 * 获取加或减
	 * @param string|int $num
	 * @return string
	 */
	protected function getNumber($num) {
		if ($num >= 0) {
			$num = '+'.$num;
		}
		return $num;
	}

	/**
	 * 查询一条数据
	 *
	 * @access public
	 *
	 * @param array|string $param 条件
	 * @param string $filed
	 * @param array $parameters
	 * @return array ,
	 */
	public function findOne($param, $filed = '*', $parameters = array()) {
		$result = $this->select("{$this->getWhere($param)} LIMIT 1", $filed, $parameters);
		return current($result);
	}
	
	/**
	 * 根据id 查找值
	 * @param string|integer $id
	 * @param string $filed
	 * @return array
	 */
	public function findById($id, $filed = '*') {
		$result = $this->select('WHERE id = '.intval($id).' LIMIT 1', $filed);
		return current($result);
	}

	/**
	 * 删除第一条数据
	 *
	 * @access public
	 *
	 * @param string|array $where
	 * @param array $parameters
	 * @return int 返回影响的行数,
	 * @internal param array|string $param 条件
	 */
	public function deleteValues($where, $parameters = array()) {
		return $this->delete($this->getCondition($where), $parameters);
	}

	/** 根据id删除数据
	 * @param string|integer $id
	 * @return int
	 */
	public function deleteById($id) {
		return $this->deleteValues('id = '.intval($id));
	}

	/**
	 * 查询数据
	 *
	 * @access public
	 *
	 * @param array|null $param 条件
	 * @param array $field 要显示的字段
	 * @param array $parameters
	 * @return array 返回查询结果,
	 */
	public function find($param = array(), $field = array(), $parameters = array()) {
		return $this->select($this->getBySort($param), $this->getField($field), $parameters);
	}

	/**
	 * 根据关键字顺序组合
	 * @param array|string $param
	 * @return string
	 */
	protected function getBySort($param) {
		if (empty($param)) {
			return null;
		}
		if (!is_array($param)) {
			return $param;
		}
		$sql = '';
		foreach ($this->sequence as $item) {
			if (!isset($param[$item]) || empty($param[$item])) {
				continue;
			}
			$method = 'get'.ucfirst($item);
			$sql .= ' '.$this->$method($param[$item]);
		}
		return $sql;
	}

	/**
	 * @param string $param ALL|null
	 * @return string
	 */
	protected function getUnion($param) {
		return 'UNION '.$param;
	}

	protected function getHaving($param) {
		if (empty($param)) {
			return null;
		}
		return 'Having '.$this->getCondition($param);
	}

	protected $operators = array(
		'=', '<', '>', '<=', '>=', '<>', '!=',
		'in', 'not in', 'is', 'is not',
		'like', 'like binary', 'not like', 'between', 'not between', 'ilike',
		'&', '|', '^', '<<', '>>',
		'rlike', 'regexp', 'not regexp',
		'~', '~*', '!~', '!~*', 'similar to',
		'not similar to'
	);

	/**
	 * 合并where 或 having 的条件
	 * @param array|string $param
	 * @return string
	 */
	protected function getCondition($param) {
		if (is_string($param)) {
			return $param;
		}
		$sql = '';
		foreach ($param as $key => $value) {
			$val = $value;
			if (!is_numeric($key)) {
				$val = (array)$val;
				array_unshift($val, $key);
			}
			$sql .= $this->getConditionOne($val);
		}
		if (empty($sql)) {
			return null;
		}
		return substr($sql, 4);
	}

	/**
	 * 合成一条条件语句
	 * @param string|array $arg
	 * @return null|string
	 */
	protected function getConditionOne($arg) {
		if (is_string($arg)) {
			return $this->getConditionLink($arg);
		}
		if (!is_array($arg)) {
			return null;
		}
		$length = count($arg);
		if ($length == 1) {
			// 'a = b'
			return $this->getConditionLink($arg[0]);
		}
		if ($length == 2) {
			if ($this->isOrOrAnd($arg[1])) {
				// ['a = b', 'or']
				return $this->getConditionLink($arg[0], $arg[1]);
			}
			// ['a', 'b']
			return $this->getConditionLink(
				"{$arg[0]} = ". $this->getValueByOperator($arg[1]));
		}
		if ($length == 3) {
			if (in_array($arg[1], $this->operators)) {
				// ['a', '=', 'b']
				return $this->getConditionLink(
					"{$arg[0]} {$arg[1]} ". $this->getValueByOperator($arg[2], $arg[1]));
			}
			// ['a', 'b', 'or']
			return $this->getConditionLink(
				"{$arg[0]} = ". $this->getValueByOperator($arg[1]), $arg[2]);
		}
		if ($length == 4) {
			if ($this->isOrOrAnd($arg[3])) {
				// ['a', '=', 'b', 'or']
				return $this->getConditionLink(
					$arg[0].' '.$arg[1]. ' '. $this->getValueByOperator($arg[2], $arg[1]), 
					$arg[3]);
			}
			// ['a', 'between', 'b', 'c']
			return $this->getConditionLink(
				$arg[0].' '.$arg[1]. ' '. $this->getValueByOperator($arg[2]). ' AND '.$this->getValueByOperator($arg[3]));
		}

		if ($length == 5) {
			if ($this->isOrOrAnd($arg[4])) {
				//['a', 'between', 'b', 'c', 'or']
				return $this->getConditionLink(
					$arg[0].' '.$arg[1]. ' '. $this->getValueByOperator($arg[2]). ' AND '.$this->getValueByOperator($arg[3]), 
					$arg[4]);
			}
			//['a', 'between', 'b', 'and', 'c']
			return $this->getConditionLink(
				$arg[0].' '.$arg[1]. ' '. $this->getValueByOperator($arg[2]). ' '.$arg[3].' '.$this->getValueByOperator($arg[4]));
		}
		//['a', 'between', 'b', 'and', 'c', 'or']
		return $this->getConditionLink(
			$arg[0].' '.$arg[1]. ' '. $this->getValueByOperator($arg[2]). ' '.$arg[3].' '.$this->getValueByOperator($arg[4]), 
			$arg[5]);
	}

	protected function getValueByOperator($value, $operator = null) {
		if (('is' == $operator || 'is not' == $operator) && is_null($value)) {
			return 'null';
		}
		if (('in' == $operator || 'not in' == $operator) && is_array($value)) {
			return "('".implode("', '", $value). "')";
		}
		// [a, int]
		if (is_array($value)) {
			if (count($value) == 1) {
				$value[] = 'string';
			}
			switch ($value[1]) {
				case 'int':
				case 'integer':
				case 'numeric':
					return intval($value[0]);
				case 'bool':
				case 'boolean':
					return boolval($value[0]);
				case 'string':
				default:
					return "'". addslashes($value[0]). "'";
			}
		}
		// 连接查询
		if (strpos($value, '.') !== false) {
			return $value;
		}
		// 表内字段关联
		if (strpos($value, '@') === 0) {
			return substr($value, 1);
		}
		return "'{$value}'";
	}

	/**
	 * 判断是否是or 或 and 连接符
	 * @param string $arg
	 * @return bool
	 */
	protected function isOrOrAnd($arg) {
		return in_array(strtolower($arg[1]), array('and', 'or'));
	}

	/**
	 * 把连接符换成标准格式
	 * @param string $arg
	 * @param string $tag
	 * @return null|string
	 */
	protected function getConditionLink($arg, $tag = 'and') {
		if (empty($arg)) {
			return null;
		}
		if (strtolower($tag) === 'or') {
			return ' OR '.$arg;
		}
		return ' AND '.$arg;
	}

	protected function getWhere($param) {
		if (empty($param)) {
			return null;
		}
		return 'WHERE '.$this->getCondition($param);
	}

	protected function getLeft(array $param) {
		return 'LEFT '.$this->getJoin($param);
	}

	protected function getInner(array $param) {
		return 'INNER '.$this->getJoin($param);
	}

	protected function getRight(array $param) {
		return 'RIGHT '.$this->getJoin($param);
	}

	protected function getJoin(array $param) {
		return 'JOIN '.$this->addPrefix($param[0]).' ON '.$param[1];
	}

	/**
	 * @param array|string $param
	 * 关键字 DISTINCT 唯一 AVG() COUNT() FIRST() LAST() MAX()  MIN() SUM() UCASE() 大写  LCASE()
	 * MID(column_name,start[,length]) 提取字符串 LEN() ROUND() 舍入 NOW() FORMAT() 格式化
	 * @return string
	 */
	protected function getField($param) {
		if (empty($param)) {
			return '*';
		}
		$result = '';
		foreach ((array)$param as $key => $item) {
			if (is_integer($key)) {
				$result .= $item .',';
			} else {
				$result .= "{$item} AS {$key},";
			}
		}
		return substr($result, 0, -1);
	}

	protected function getGroup($param) {
		return 'GROUP BY '.implode(',', (array)$param);
	}

	protected function getOrder($param) {
		if (is_string($param)) {
			return 'ORDER BY '.$param;
		}
		$result = 'ORDER BY ';
		foreach ($param as $item) {
			if (is_array($item)) {
				$result .= $item[0] .' '.strtoupper($item[1]).',';
			} else {
				$result .= $item.',';
			}
		}
		return substr($result, 0, -1);
	}

	protected function getLimit($param) {
		$param = (array)$param;
		if (count($param) == 1) {
			return "LIMIT {$param[0]}";
		}
		return "LIMIT {$param[0]},{$param[1]}";
	}

	protected function getOffset($param) {
		return "OFFSET {$param}";
	}

	/**
	 * 总记录
	 *
	 * @access public
	 *
	 * @param array|null $param 条件
	 * @param string $field 默认为id
	 * @param array $parameters
	 * @return int 返回总数,
	 */
	public function count($param = array(), $field = 'id', $parameters = array()) {
		$result = $this->select($this->getWhere($param). ' LIMIT 1', "COUNT({$field}) AS count", $parameters);
		if (empty($result)) {
			return null;
		}
		return $result[0]['count'];
	}

	/**
	 * 获取第一行第一列
	 * @param array|string $param
	 * @param string $filed
	 * @param array $parameters
	 * @return string
	 */
	public function scalar($param, $filed = '*', $parameters = array()) {
		$result = $this->select($this->getBySort($param), $filed, $parameters);
		if (empty($result)) {
			return false;
		}
		return current($result[0]);
	}
	
	/**
	 * 执行SQL语句
	 *
	 * @access public
	 *
	 * @param array $param 条件
	 * @param array $parameters 值
	 * @return array 返回查询结果,
	 */
	public function findByHelper($param, $parameters = array()) {
		if (empty($param)) {
			return array();
		}
		$sql  = (new SqlFilter($this->prefix))->getSQL($param);
		return $this->db->getArray($sql, $parameters);
	}

	/**
	 * 拷贝（未实现）
	 */
	public function copy() {
		return $this->select(null, '* INTO table in db');
	}

	/**
	 * 查询
	 * @param $sql
	 * @param string $field
	 * @param array $parameters
	 * @return mixed
	 */
	public function select($sql, $field = '*', $parameters = array()) {
		return $this->db->getArray("SELECT {$field} FROM {$this->table} {$sql}", $parameters);
	}

	/**
	 * 插入
	 * @param string $columns
	 * @param string $tags
	 * @param array $parameters
	 * @return int
	 */
	public function insert($columns, $tags, $parameters = array()) {
		return $this->db->insert("INSERT INTO {$this->table} ({$columns}) VALUES ({$tags})", $parameters);
	}

	/**
	 * 如果行作为新记录被insert，则受影响行的值为1；如果原有的记录被更新，则受影响行的值为2。 如果有多条存在则只更新最后一条
	 * @param string $columns
	 * @param string $tags
	 * @param string $update
	 * @param array $parameters
	 * @return int
	 */
	public function insertOrUpdate($columns, $tags, $update, $parameters = array()) {
		return $this->db->update("INSERT INTO {$this->table} ({$columns}) VALUES ({$tags}) ON DUPLICATE KEY UPDATE {$update}", $parameters);
	}

	/**
	 *在执行REPLACE后，系统返回了所影响的行数，如果返回1，说明在表中并没有重复的记录，如果返回2，说明有一条重复记录，系统自动先调用了 DELETE删除这条记录，然后再记录用INSERT来insert这条记录。如果返回的值大于2，那说明有多个唯一索引，有多条记录被删除和insert。
	 * @param string $columns
	 * @param string $tags
	 * @param array $parameters
	 * @return int
	 */
	public function insertOrReplace($columns, $tags, $parameters = array()) {
		return $this->update("REPLACE INTO {$this->table} ({$columns}) VALUES ({$tags})", $parameters);
	}

	/**
	 * 更新
	 * @param string $columns
	 * @param string $where
	 * @param array $parameters
	 * @return int
	 */
	public function update($columns, $where, $parameters = array()) {
		return $this->db->update("UPDATE {$this->table} SET {$columns} WHERE {$where}", $parameters);
	}

	/**
	 * 删除
	 * @param string $where
	 * @param array $parameters
	 * @return int
	 */
	public function delete($where, $parameters = array()) {
		return $this->db->delete("DELETE FROM {$this->table} WHERE {$where}", $parameters);
	}
	
	public function execute($sql, $parameters = array()) {
		return $this->db->execute($sql, $parameters);
	}

	/**
	 * 获取错误信息
	 */
	public function getError() {
		return $this->db->getError();
	}
}