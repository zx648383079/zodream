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
	public function add($addData) {
		$addFields = array();
		$addValues = array();
		foreach ($addData as $key => $value) {
			$addFields[] = $key;
			$addValues[] = $value;
		}
		$addFields = implode('`,`', $addFields);
		$addValues = implode("','", $addValues);
		$sql       = "INSERT INTO {$this->table} (`$addFields`) VALUES ('$addValues')";
		return $this->db->insert($sql);
	}
	 
	/**
	 * 修改记录
	 *
	 * @access public
	 *
	 * @param array $param 条件
	 * @param array $updateData 需要修改的内容
	 * @return int 返回影响的行数,
	 */
	public function update($updateData , $param) {
		$where = $setData = '';
		if (is_array($param)) {
			foreach ($param as $key => $value) {
				$where .= $value.' AND ';
			}
			$where = 'WHERE '.substr($where, 0, -4);
		} else {
			$where = 'WHERE '.$param;
		}
		foreach ($updateData as $key => $value) {
			$setData .= "`$key` = '$value',";
		}
		$setData = substr($setData, 0, -1);
		$sql     = "UPDATE {$this->table} SET {$setData} {$where}";
		return $this->db->update($sql);
	}
	
	/**
	 * 更具id 修改记录
	 * @param string|integer $id
	 * @param array $data
	 */
	public function updateById($id, $data) {
		return $this->update($data, 'id = '.$id);
	}
	 
	/**
	 * 设置bool值
	 *
	 * @param string $filed
	 * @param string $where
	 * @return int
	 */
	public function updateBool($filed, $where) {
		$sql =  "UPDATE {$this->table} SET {$filed} = CASE WHEN {$filed} = 1 THEN 0 ELSE 1 END WHERE ";
		$sql .= $where;
		return $this->db->update($sql);
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
		$sql = "UPDATE {$this->table} SET {$filed} = {$filed} {$num} WHERE $where";
		return $this->db->update($sql);
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
		$where = '';
		if ( is_array($param) ) {
			foreach ($param as $key => $value) {
				if (is_numeric($key)) {
					$where .= $value.' AND ';
				} else {
					$where .= $key." = '{$value}' AND ";
				}
			}
			$where = 'WHERE '.substr($where, 0, -4);
		} else if (is_string($param)) {
			$where = 'WHERE '.$param;
		}
		$sql    = "SELECT {$filed} FROM {$this->table} {$where} LIMIT 1";
		$result = $this->db->select($sql);
		return array_shift($result);
	}
	
	/**
	 * 根据id 查找值
	 * @param string|integer $id
	 * @param string $filed
	 * @return array
	 */
	public function findById($id, $filed = '*') {
		$sql = "SELECT {$filed} FROM {$this->table} WHERE id = {$id} LIMIT 1";
		$result = $this->db->select($sql);
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
	public function delete($param) {
		$where = '';
		if(is_array($param)) {
			foreach ($param as $key => $value) {
				$where .= $value.' AND ';
			}
			$where = 'WHERE '.substr($where, 0, -4);
		} else {
			$where = 'WHERE '.$param;
		}
		$sql = "DELETE FROM {$this->table} $where";
		return $this->db->delete($sql);
	}

	/** 根据id删除数据
	 * @param string|integer $id
	 * @return int
	 */
	public function deleteById($id) {
		return $this->delete('id = '.$id);
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
		$limit = $order = $group = $where = $like = '';
		if (is_array($param) && !empty($param)) {
			$limit = isset($param['limit']) ? 'LIMIT '.$param['limit'] : '';
			$order = isset($param['order']) ? 'ORDER BY '.$param['order'] : '';
			$group = isset($param['group']) ? 'GROUP BY '.$param['group'] : '';
			if (isset($param['where'])) {
				foreach ((array)$param['where'] as $value) {
					if (empty($where)) {
						$where = 'WHERE '.$value;
					} else {
						if (is_array($value)) {
							switch ($value[1]) {
								case 'or':
									$where .= ' OR '.$value[0];
									break;
								case 'and':
									$where .= ' AND '.$value[0];
									break;
							}
						} else {
							$where .= ' AND '.$value;
						}
					}
				}
			}
		}
		$selectFields = empty($field) ? '*' : implode(',', (array)$field);
		$sql    = "SELECT $selectFields FROM {$this->table} $where $group $order $limit";
		$this->db->execute($sql);
		return $this->db->select($sql);
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
		$where = '';
		if (is_array($param) && !empty($param)) {
			foreach ($param as $value) {
				$where .= $value.' AND ';
			}
			$where = 'WHERE '.substr($where, 0, -4);
		} elseif(is_string($param)) {
			$where = 'WHERE '.$param;
		}
		$sql  = "SELECT COUNT({$field}) as count FROM {$this->table} $where";
		$result = $this->db->select($sql);
		return $result[0]['count'];
	}
	 
	/**
	 * 得到下一个id
	 *
	 * @access public
	 *
	 * @return string 返回id,
	 */
	public function nextId() {
		$sql  = "SHOW TABLE STATUS LIKE '{$this->table}'";
		$stmt = $this->db->execute($sql);
		return $stmt->fetchObject()->Auto_increment;
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
	 * 获取错误信息
	 */
	public function getError() {
		return $this->db->getError();
	}
}