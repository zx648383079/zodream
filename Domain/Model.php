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

abstract class Model {
	/**
	 * @var \Zodream\infrastructure\Database\Database
	 */
	protected $db;

	protected $table;

	protected $fillAble = array();
	
	protected $prefix;
	
	public function __construct() {
		$configs = Config::getInstance()->get('db');
		$this->db = call_user_func(array($configs['driver'], 'getInstance'), $configs);
		$this->prefix = $configs['prefix'];
		if (isset($this->table)) {
			$this->table = $this->prefix. $this->table;
		}
	}

	/**
	 * 填充数据 自动识别添加或修改
	 * 添加 有一个数组参数 或 多个 非数组参数（与fillAble字段对应）
	 * 修改 有两个参数 第一个为数组 第二个为条件,如果第二个参数是数字，则为id
	 * 关联数组参数不需要一一对应，自东根据 fillAble 取需要的
	 */
	public function fill() {
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
		$addValues = implode("','", array_values($addData));
		return $this->insert("`{$addFields}`", "'{$addValues}'");
	}
	 
	/**
	 * 修改记录
	 *
	 * @access public
	 *
	 * @param array $param 条件 默认使用AND 连接
	 * @param array $updateData 需要修改的内容
	 * @return int 返回影响的行数,
	 */
	public function updateValues($updateData, $param) {
		$setData = '';
		foreach ($updateData as $key => $value) {
			$setData .= "`$key` = '$value',";
		}
		$setData = substr($setData, 0, -1);
		return $this->update($setData, $this->getWhere($param));
	}
	
	/**
	 * 更具id 修改记录
	 * @param string|integer $id
	 * @param array $data
	 */
	public function updateById($id, $data) {
		return $this->updateValues($data, 'id = '.$id);
	}
	 
	/**
	 * 设置bool值
	 *
	 * @param string $filed
	 * @param string $where
	 * @return int
	 */
	public function updateBool($filed, $where) {
		return $this->update("{$filed} = CASE WHEN {$filed} = 1 THEN 0 ELSE 1 END", $where);
	}
	
	/**
	 * int加减
	 *
	 * @param string $filed
	 * @param string $where
	 * @param string $num
	 * @return int
	 */
	public function updateOne($filed, $where, $num = 1) {
		if ($num >= 0) {
			$num = '+'.$num;
		}
		return $this->update("{$filed} = {$filed} {$num}", $where);
	}

	/**
	 * 查询一条数据
	 *
	 * @access public
	 *
	 * @param array|string $param 条件
	 * @return array,
	 */
	public function findOne($param, $filed = '*') {
		$result = $this->select("{$this->getWhere($param)} LIMIT 1", $filed);
		return array_shift($result);
	}
	
	/**
	 * 根据id 查找值
	 * @param string|integer $id
	 * @param string $filed
	 * @return array
	 */
	public function findById($id, $filed = '*') {
		$result = $this->select("WHERE id = {$id} LIMIT 1", $filed);
		return array_shift($result);
	}
	 
	/**
	 * 删除第一条数据
	 *
	 * @access public
	 *
	 * @param array|string $param 条件
	 * @return int 返回影响的行数,
	 */
	public function deleteValues($param) {
		return $this->delete($this->getWhere($param));
	}

	/** 根据id删除数据
	 * @param string|integer $id
	 * @return int
	 */
	public function deleteById($id) {
		return $this->deleteValues('id = '.$id);
	}
	 
	/**
	 * 查询数据
	 *
	 * @access public
	 *
	 * @param array $field 要显示的字段
	 * @param array|null $param 条件
	 * @return array 返回查询结果,
	 */
	public function find($param = array(), $field = array()) {
		//$offset = $limit = $order = $group = $where = $having = $left = $right = $inner = $join = '';
		$sequence = array('join', 'left', 'inner', 'right', 'where', 'group', 'having', 'order', 'limit', 'offset');
		return $this->select($this->getBySort($sequence, $param), $this->getField($field));
	}

	/**
	 * 根据关键字顺序组合
	 * @param array $sequence
	 * @param array|string $param
	 * @return string
	 */
	protected function getBySort(array $sequence, $param) {
		if (!is_array($param) || empty($param)) {
			return $param;
		}
		$sql = '';
		foreach ($sequence as $item) {
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
		return 'Having '.$this->getCondition($param);
	}

	/**
	 * 合并where 或 having 的条件
	 * @param array|string $param
	 */
	protected function getCondition($param) {
		if (is_string($param)) {
			return $param;
		}
		$sql = '';
		foreach ($param as $value) {
			if (is_array($value)) {
				switch ($value[1]) {
					case 'or':
						$sql .= ' OR '.$value[0];
						break;
					case 'and':
						$sql .= ' AND '.$value[0];
						break;
				}
			} else {
				$sql .= ' AND '.$value;
			}
		}
		return substr($sql, 4);
	}

	protected function getWhere($param) {
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
		return "JOIN {$param[0]} ON {$param[1]}";
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
	 * @return int 返回总数,
	 */
	public function count($param = array(), $field = 'id') {
		$result = $this->select($this->getWhere($param), "COUNT({$field}) AS count");
		return $result[0]['count'];
	}
	
	/**
	 * 执行SQL语句
	 *
	 * @access public
	 *
	 * @param array $param 条件
	 * @param bool $isList 返回类型
	 * @return array 返回查询结果,
	 */
	public function findByHelper($param, $isList = TRUE) {
		if (empty($param)) {
			return array();
		}
		$sql  = (new SqlFilter($this->prefix))->getSQL($param);
		if ($isList) {
			return $this->db->getArray($sql);
		}
		return $this->db->getObject($sql);
	}

	/**
	 * 拷贝（未实现）
	 */
	public function copy() {
		return $this->select(null, '* INTO table in db');
	}

	/**
	 * @param $sql
	 * @param string $field
	 * @param array $parameters
	 * @return mixed
	 */
	public function select($sql, $field = '*', $parameters = array()) {
		return $this->db->getArray("SELECT {$field} FROM {$this->table} {$sql}", $parameters);
	}

	public function insert($columns, $tags, $parameters = array()) {
		return $this->db->insert("INSERT INTO {$this->table} ({$columns}) VALUES ({$tags})", $parameters);
	}

	public function update($columns, $where, $parameters = array()) {
		return $this->db->update("UPDATE {$this->table} SET {$columns} WHERE {$where}", $parameters);
	}

	public function delete($where, $parameters = array()) {
		return $this->db->delete("DELETE FROM {$this->table} WHERE {$where}", $parameters);
	}

	/**
	 * 获取错误信息
	 */
	public function getError() {
		return $this->db->getError();
	}
}