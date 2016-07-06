<?php 
namespace Zodream\Domain;
/**
* 数据基类
* 
* @author Jason
*/
use Zodream\Domain\Filter\DataFilter;
use Zodream\Infrastructure\Database\Command;
use Zodream\Infrastructure\Database\Query;
use Zodream\Infrastructure\EventManager\Action;
use Zodream\Infrastructure\MagicObject;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Request;

abstract class Model extends MagicObject {

	const BEFORE_SAVE = 'before save';
	const AFTER_SAVE = 'after save';
	const BEFORE_INSERT = 'before insert';
	const AFTER_INSERT = 'after insert';
	const BEFORE_UPDATE = 'before update';
	const AFTER_UPDATE = 'after update';
	
	protected $errors = [];

	/**
	 * 过滤规则
	 * @return array
	 */
	protected function rules() {
		return [];
	}

	/**
	 * 标签
	 * @return array
	 */
	protected function labels() {
		return [];
	}

	/**
	 * 行为
	 * @return array
	 */
	protected function behaviors() {
		return [];
	}

	/**
	 * 表名
	 * @var string
	 */
	public static $table;

	/**
	 * 主键
	 * @var string
	 */
	protected $primaryKey = [
		'id'
	];

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
		$this->command->setTable(self::$table);
	}
	
	public function load($data = null, $key = null) {
		if (is_string($data)) {
			$key = $data;
		}
		if (!is_array($data)) {
			$data = Request::post($key);
		}
		if (empty($data)) {
			return false;
		}
		$this->set($data);
		return true;
	}

	public function set($key, $value = null){
		if (empty($key)) {
			return $this;
		}
		if (!is_array($key)) {
			$key = [$key => $value];
		}
		$keys = array_keys($this->rules());
		$keys = array_merge($keys, $this->primaryKey);
		foreach ($key as $k => $item) {
			if (property_exists($this, $k)) {
				$this->$k = $item;
				continue;
			}
			if (in_array($k, $keys)) {
				$this->_data[$k] = $item;
			}
		}
		return $this;
	}

	public function get($key = null, $default = null){
		if (is_null($key)) {
			return $this->_data;
		}
		if ($this->has($key)) {
			return $this->_data[$key];
		}
		return $default;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	public function getLabel($key) {
		$labels = $this->labels();
		if (array_key_exists($key, $labels)) {
			return $labels[$key];
		}
		return null;
	}
	
	public function save() {
		$this->runBehavior(self::BEFORE_SAVE);
		if ($this->has($this->primaryKey)) {
			$row = $this->update();
		} else {
			$row = $this->insert();
		}
		$this->runBehavior(self::BEFORE_SAVE);
		return $row;
	}

	protected function runBehavior($key) {
		if (empty($key)) {
			return;
		}
		$behaviors = $this->behaviors();
		if (!array_key_exists($key, $behaviors)) {
			return;
		}
		if (!is_array($behaviors[$key])) {
			$behaviors[$key] = [$behaviors[$key]];
		}
		foreach ($behaviors[$key] as $item) {
			(new Action($item))->run($this);
		}
	}
	
	protected function validate() {
		DataFilter::validate($this->get(), $this->rules());
		$this->errors = DataFilter::getError();
		foreach ($this->rules() as $key => $item) {
			if (method_exists($this, $item)) {
				$this->$item($this->get($key));
			}
		}
		return empty($this->errors);
	}

	/**
	 * @param string $table
	 * @param string $link
	 * @param string $key
	 * @return array
	 */
	public function hasOne($table, $link, $key = null) {
		if (!is_null($key)) {
			$key = $link;
			$link = 'id';
		}
		return (new Query())
			->from($table)
			->where([$link => $this->get($key)])
			->one();
	}

	/**
	 * @param string $table
	 * @param string $link
	 * @param string $key
	 * @return array
	 */
	public function hasMany($table, $link, $key) {
		return (new Query())
			->from($table)
			->where([$link => $this->get($key)])
			->all();
	}

	public function insert() {
		if (!$this->validate()) {
			return false;
		}
		$this->runBehavior(self::BEFORE_INSERT);
		$row = $this->add($this->get());
		if (!empty($row)) {
			$this->set('id', $row);
		}
		$this->runBehavior(self::AFTER_INSERT);
		return $row;
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
		return $this->command
			->insert("`{$addFields}`", StringExpand::repeat('?', count($addData)), 
				array_values($addData));
	}

	public function addValues(array $columns, array $values = null) {
		$results = array();
		if (is_null($values)) {
			$values = array_values($columns);
			$columns = array_keys($columns);
			$count = 1;
			foreach ($values as &$item) {
				if (!is_array($item)) {
					$item = [$item];
				}
				if (count($item) > $count) {
					$count = count($item);
				}
			}
			for ($i = 0; $i < $count; $i ++) {
				$result = [];
				foreach ($values as $item) {
					if (count($item) > $i) {
						$result[] = $item[$i];
						continue;
					}
					$result[] = $item[count($item) - 1];
				}
				$results[] = "'".implode("','", $result)."'";;
			}
		} else {
			foreach ($values as $value) {
				$results[] = "'".implode("','", $value)."'";
			}
		}
		return $this->command->insert('`'.implode('`,`', $columns).'`', implode('),(', $results));
	}
	 
	/**
	 * 修改记录
	 *
	 * @param array|string $where 条件 默认使用AND 连接
	 * @param array $args 需要修改的内容
	 * @return int 返回影响的行数,
	 */
	public function update($where = null, $args = null) {
		if (is_null($where)) {
			$where = [$this->primaryKey[0] => $this->get($this->primaryKey[0])];
		}
		if (is_array($args)) {
			$this->set($args);
		}
		if (!$this->validate()) {
			return false;
		}
		$this->runBehavior(self::BEFORE_UPDATE);
		$data = [];
		$parameters = array();
		foreach ($this->get() as $key => $value) {
			$data[] = ["`$key` = ?"];
			$parameters[] = $value;
		}
		$row = $this->command->update(implode(',', $data), $this->getQuery(
			array(
				'where' => $where
			)), $parameters);
		$this->runBehavior(self::AFTER_UPDATE);
		return $row;
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
	public static function findOne($param, $field = '*', $parameters = array()) {
		$model = new static;
		if (is_numeric($param)) {
			$param = [$model->primaryKey[0] => $param];
		}
		if (!array_key_exists('where', $param)) {
			$param = [
				'where' => $param
			];
		}
		$model->set(static::find()
			->load($param)
			->select($field)
			->addParam($parameters)
			->one());
		return $model;
	}

	/**
	 * 删除数据
	 * 
	 * @param string|array $where 条件
	 * @param array $parameters
	 * @return int 返回影响的行数,
	 */
	public function delete($where = null, $parameters = array()) {
		if (is_null($where)) {
			$where = [$this->primaryKey[0] => $this->get($this->primaryKey[0])];
		}
		return $this->command
			->delete($this->getQuery(['where' => $where]), $parameters);
	}

	/**
	 * 查询数据
	 *
	 * @access public
	 *
	 * @return Query 返回查询结果,
	 */
	public static function find() {
		return (new Query())->from(static::$table);
	}

	/**
	 * @param array $param
	 * @param string $field
	 * @param array $parameters
	 * @return static[]
	 */
	public function findAll($param = array(), $field = '*', $parameters = array()) {
		if (!is_array($param) ||
			(!array_key_exists('where', $param) &&
			!array_key_exists('group', $param) &&
			!array_key_exists('order', $param) &&
			!array_key_exists('having', $param)) ) {
			$param = array(
				'where' => $param
			);
		}
		$data = static::find()
			->load($param)
			->select($field)
			->addParam($parameters)
			->all();
		$args = [];
		foreach ($data as $item) {
			$model = new static;
			$model->set($item);
			$args[] = $model;
		}
		return $args;
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
		$result = $this->command
			->select($this->getQuery($param), $this->getField($filed), $parameters);
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

	
	public function getError($key = null) {
		if (empty($key)) {
			return self::$_error;
		}
		if (!array_key_exists($key, self::$_error)) {
			return array();
		}
		return self::$_error[$key];
	}
	
	public function getFirstError($key) {
		if (!array_key_exists($key, self::$_error)) {
			return null;
		}
		return current(self::$_error[$key]);
	}

	protected function setError($key, $error) {
		if (!array_key_exists($key, self::$_error)) {
			self::$_error[$key] = array();
		}
		self::$_error[$key][] = $error;
	}
}