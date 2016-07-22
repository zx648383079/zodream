<?php
namespace Zodream\Domain\Model;
/**
 * 数据基类
 *
 * @author Jason
 */
use Zodream\Domain\Filter\DataFilter;
use Zodream\Domain\Filter\ModelFilter;
use Zodream\Infrastructure\Database\Command;
use Zodream\Infrastructure\Database\Query;
use Zodream\Infrastructure\Database\Record;
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
	
	public $isNewRecord = true;
	
	protected $_oldData = [];

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
	/**
	 * @var Command
	 */
	protected $command;

	public function __construct() {
		$this->command = Command::getInstance();
		$this->command->setTable(self::$table);
		$this->init();
	}
	
	public function init() {
		
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
		$this->setOldData();
		if (!is_array($key)) {
			$key = [$key => $value];
		}
		foreach ($key as $k => $item) {
			if (property_exists($this, $k)) {
				$this->$k = $item;
				continue;
			}
			$this->_data[$k] = $item;
		}
		return $this;
	}
	
	protected function setOldData() {
		if ($this->isNewRecord || !empty($this->_oldData)) {
			return;
		}
		$this->_oldData = $this->_data;
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
	 * @param string|array $key
	 * @return bool
	 */
	public function has($key = null) {
		if (!is_array($key)) {
			return parent::has($key);
		}
		foreach ($key as $item) {
			if (array_key_exists($item, $this->_data)) {
				return true;
			}
		}
		return false;
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
		return ucwords(str_replace('_', ' ', $key));
	}

	public function save() {
		$this->runBehavior(self::BEFORE_SAVE);
		if ($this->isNewRecord) {
			$row = $this->insert();
		} else {
			$row = $this->update();
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

	/**
	 * 验证
	 * @param array $rules
	 * @return bool
	 */
	public function validate($rules = array()) {
		if (empty($rules)) {
			$rules = $this->rules();
		}
		$result = ModelFilter::validate($this, $rules);
		return $result && empty($this->errors);
	}

	/**
	 * @param bool $all 是否包含主键唯一等字段的值
	 * @return array
	 */
	protected function getValues($all = true) {
		$keys = array_keys($this->rules());
		if ($all) {
			$keys = array_merge($keys, $this->primaryKey);
		}
		$data = [];
		foreach ($this->get() as $k => $item) {
			if (in_array($k, $keys) && !property_exists($this, $k)) {
				$data[$k] = $item;
			}
		}
		return $data;
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
		$row = $this->add($this->getValues());
		if (!empty($row)) {
			$this->set('id', $row);
		}
		$this->runBehavior(self::AFTER_INSERT);
		return $row;
	}
	
	protected function getRecord() {
		return (new Record())->from(static::$table);
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
		return $this->getRecord()->load($addData)->insert();
	}

	public function addValues(array $columns, array $values = null) {
		return $this->getRecord()
			->batchInsert($columns, $values);
	}

	/**
	 * 自动获取条件
	 * @return array
	 */
	protected function getWhereKey() {
		foreach ($this->primaryKey as $item) {
			if ($this->has($item)) {
				return [$item => $this->get($item)];
			}
		}
		return fasle;
	}

	/**
	 * 获取需要更新的数据
	 * @return array
	 */
	protected function getUpdateData() {
		if ($this->isNewRecord) {
			return $this->_data;
		}
		$data = [];
		foreach ($this->_data as $key => $item) {
			if (array_key_exists($key, $this->_oldData) 
				&& $item === $this->_oldData[$key]) {
				continue;
			}
			$data[$key] = $item;
		}
		return $data;
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
			$where = $this->getWhereKey();
		}
		if (is_array($args)) {
			$this->set($args);
		}
		if (!$this->validate()) {
			return false;
		}
		$this->runBehavior(self::BEFORE_UPDATE);
		$row = $this->getRecord()
			->load($this->getUpdateData())
			->whereMany($where)
			->update();
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
		return $this->getRecord()
			->set(null, "{$filed} = CASE WHEN {$filed} = 1 THEN 0 ELSE 1 END")
			->whereMany($where)->update();
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
		return $this->getRecord()
			->set($sql)
			->whereMany($where)
			->update();
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
	 * SELECT ONE BY QUERY
	 * 查询一条数据
	 *
	 * @access public
	 *
	 * @param array|string $param 条件
	 * @param string $field
	 * @param array $parameters
	 * @return static|bool
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
		$data = static::find()
			->load($param)
			->select($field)
			->addParam($parameters)
			->one();
		if (empty($data)) {
			return false;
		}
		$model->set($data);
		$model->isNewRecord = false;
		return $model;
	}

	/**
	 * 删除数据
	 * DELETE QUERY
	 *
	 * @param string|array $where 条件
	 * @param array $parameters
	 * @return int 返回影响的行数,
	 */
	public function delete($where = null, $parameters = array()) {
		if (is_null($where)) {
			$where = $this->getWhereKey();
		}
		return $this->getRecord()
			->whereMany($where)
			->addParam($parameters)
			->delete();
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
	public static function findAll($param = array(), $field = '*', $parameters = array()) {
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
			$model->isNewRecord = false;
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
	public static function count(array $param = array(), $field = 'id', $parameters = array()) {
		if (!array_key_exists('where', $param)) {
			$param = array(
				'where' => $param
			);
		}
		return static::find()->load($param)
			->addParam($parameters)
			->count($field)->limit(1)->scalar();
	}

	public function getError($key = null) {
		if (empty($key)) {
			return $this->errors;
		}
		if (!array_key_exists($key, $this->errors)) {
			return array();
		}
		return $this->errors[$key];
	}

	public function getFirstError($key) {
		if (!array_key_exists($key, $this->errors)) {
			return null;
		}
		return current($this->errors[$key]);
	}

	public function setError($key, $error = null) {
		if (is_array($key) && is_null($error)) {
			$this->errors = array_merge($this->errors, $key);
			return;
		}
		if (!array_key_exists($key, $this->errors)) {
			$this->errors[$key] = array();
		}
		$this->errors[$key][] = $error;
	}
}