<?php 
namespace Zodream\Domain;
/**
* 数据基类
* 
* @author Jason
*/
use Zodream\Infrastructure\Database\Command;
use Zodream\Infrastructure\Database\Query;
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

abstract class Model {
	protected $fillAble = array();
	
	protected $table;

	public function setTable($table) {
		$this->command->setTable($table);
		return $this;
	}
	/**
	 * @var Command
	 */
	protected $command;
	
	public function __construct() {
		$this->command = Command::getInstance();
		$this->command->setTable($this->table);
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
		return $this->update($data, $param);
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
		return $this->command->insert("`{$addFields}`", StringExpand::repeat('?', count($addData)), array_values($addData));
	}

	public function addValues(array $columns, array $values) {
		$results = array();
		foreach ($values as $value) {
			$results[] = "'".implode("','", $value)."'";
		}
		return $this->command->insert('`'.implode('`,`', $columns).'`', implode('),(', $results));
	}
	 
	/**
	 * 修改记录
	 *
	 * @param array|string $param 条件 默认使用AND 连接
	 * @param array $updateData 需要修改的内容
	 * @return int 返回影响的行数,
	 */
	public function update(array $updateData, $param) {
		$setData = '';
		$parameters = array();
		foreach ($updateData as $key => $value) {
			if (is_numeric($key)) {
				$setData .= $value .',';
				continue;
			}
			$setData .= "`$key` = ?,";
			$parameters[] = $value;
		}
		$setData = substr($setData, 0, -1);
		return $this->command->update($setData, $this->getQuery(
			array(
				'where' => $param
			)), $parameters);
	}

	/**
	 * 更具id 修改记录
	 * @param string|integer $id
	 * @param array $data
	 * @return int
	 */
	public function updateById($id, array $data) {
		return $this->update($data, 'id = '.intval($id));
	}
	 
	/**
	 * 设置bool值
	 *
	 * @param string $filed
	 * @param string $where
	 * @return int
	 */
	public function updateBool($filed, $where) {
		return $this->command->update(
			"{$filed} = CASE WHEN {$filed} = 1 THEN 0 ELSE 1 END",
			$this->getQuery(
				array(
					'where' => $where
				)));
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
		$sql = array();
		foreach ((array)$filed as $key => $item) {
			if (is_numeric($key)) {
				$sql[] = "`$item` = `$item` ".$this->getNumber($num);
			} else {
				$sql[] = "`$key` = `$key` ".$item;
			}
		}
		return $this->command->update(implode(',', $sql), 
			$this->getQuery(
			array(
				'where' => $where
			)));
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
	 * @param string $field
	 * @param array $parameters
	 * @return array ,
	 */
	public function findOne($param, $field = '*', $parameters = array()) {
		$sql = null;
		if (!is_array($param) || !array_key_exists('where', $param)) {
			$sql = $this->getQuery(array(
				'where' => $param,
				'limit' => 1
			));
		} else {
			$param['limit'] = 1;
			$sql = $this->getQuery($param);
		}
		$result = $this->command->select($sql, $this->getField($field), $parameters);
		return current($result);
	}
	
	/**
	 * 根据id 查找值
	 * @param string|integer $id
	 * @param string $field
	 * @return array
	 */
	public function findById($id, $field = '*') {
		$result = $this->command->select('WHERE id = '.intval($id).' LIMIT 1', $this->getField($field));
		return current($result);
	}

	/**
	 * 删除数据
	 * 
	 * @param string|array $where 条件
	 * @param array $parameters
	 * @return int 返回影响的行数,
	 */
	public function delete($where, $parameters = array()) {
		return $this->command->delete($this->getQuery(['where' => $where]), $parameters);
	}

	/** 根据id删除数据
	 * @param string|integer $id
	 * @return int
	 */
	public function deleteById($id) {
		return $this->delete('id = '.intval($id));
	}

	/**
	 * 查询数据
	 *
	 * @access public
	 *
	 * @return Query 返回查询结果,
	 */
	public function find() {
		return new Query();
	}

	public function findAll($param = array(), $field = '*', $parameters = array()) {
		if (is_array($param) && 
			!array_key_exists('where', $param) &&
			!array_key_exists('group', $param) &&
			!array_key_exists('order', $param) &&
			!array_key_exists('having', $param) ) {
			$param = array(
				'where' => $param
			);
		}
		return $this->command->select($this->getQuery($param), $this->getField($field), $parameters);
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
		if (!array_key_exists('where', $param)) {
			$param = array(
				'where' => $param
			);
		}
		$result = $this->command->select($this->getQuery($param). ' LIMIT 1', "COUNT({$field}) AS count", $parameters);
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
		$result = $this->command->select($this->getQuery($param), $this->getField($filed), $parameters);
		if (empty($result)) {
			return false;
		}
		return current($result[0]);
	}

	protected function getQuery($param) {
		if (!is_array($param)) {
			return $param;
		}
		return (new Query($param))->getSql();
	}

	protected function getField($filed) {
		if (empty($filed)) {
			return '*';
		}
		$result = array();
		foreach ((array)$filed as $key => $item) {
			if (is_integer($key)) {
				$result[] = $item;
			} else {
				$result[] = "{$item} AS {$key}";
			}
		}
		return implode($result, ',');
	}

	/**
	 * 获取错误信息
	 * @return string
	 */
	public function getError() {
		return $this->command->getError();
	}
}