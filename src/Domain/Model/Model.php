<?php
namespace Zodream\Domain\Model;
/**
 * 数据基类
 *
 * @author Jason
 */
use Zodream\Domain\Filter\ModelFilter;
use Zodream\Domain\Html\Page;
use Zodream\Infrastructure\Database\Query\Record;
use Zodream\Infrastructure\Event\Action;
use Zodream\Infrastructure\Base\MagicObject;
use Zodream\Infrastructure\Http\Request;
use Zodream\Infrastructure\Traits\ErrorTrait;

abstract class Model extends MagicObject {

    use ErrorTrait;

	const BEFORE_SAVE = 'before save';
	const AFTER_SAVE = 'after save';
	const BEFORE_INSERT = 'before insert';
	const AFTER_INSERT = 'after insert';
	const BEFORE_UPDATE = 'before update';
	const AFTER_UPDATE = 'after update';
	
	public $isNewRecord = true;
	
	protected $_oldData = [];

    /**
     * GET RELATION
     * @var array
     */
	protected $relations = [];

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
	 * @return string
	 */
	public static function tableName() {
	    return '';
    }

    public static function className() {
	    return static::class;
    }

	/**
	 * 主键
	 * @var array
	 */
	protected $primaryKey = [
		'id'
	];

	public function __construct() {
		$this->init();
	}
	
	public function init() {
		
	}

    /**
     * 转载数据
     * @param null $data
     * @param null $key
     * @return bool
     */
	public function load($data = null, $key = null) {
		if (is_string($data)) {
			$key = $data;
            $data = null;
		}
		if (Request::isPost()) {
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
		$method = 'get'.ucfirst($key);
		if (!method_exists($this, $method)) {
            return $default;
        }
		$result = call_user_func([$this, $method]);
		$this->_data[$key] = $result;
		return $result;
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
	 * @param string $link $table.$link
	 * @param string $key $this.$key
	 * @return array|Model
	 */
	public function hasOne($table, $link, $key = null) {
        if ($table instanceof Model) {
            $table = $table->className();
        }
        if (!array_key_exists($table, $this->relations)) {
            $this->setRelation($table, $this->getRelationQuery($table)
                ->where($this->getRelationWhere($link, $key))
                ->one());
        }
        return $this->getRelation($table);
	}

    /**
     * GET RELATION WHERE SQL
     * @param string|array $links
     * @param string $key
     * @return array
     */
	protected function getRelationWhere($links, $key = null) {
        if (is_null($key) && !is_array($links)) {
            $key = in_array('id', $this->primaryKey) ? 'id' : current($this->primaryKey);
        }
	    if (!is_array($links)) {
	        $links = [$links => $key];
        }
        foreach ($links as &$item) {
            $item = $this->get($item);
        }
        return $links;
    }

    /**
     * GET RELATION QUERY
     * @param static $table
     * @return Query
     */
    protected function getRelationQuery($table) {
	    $query = new Query();
	    if (class_exists($table)) {
	        return $query->setModelName($table)
                ->from(call_user_func($table.'::tableName'));
        }
	    return $query->from($table);
    }

	/**
	 * @param string $table
	 * @param string $link $table.$link
	 * @param string $key $this.$key
	 * @return array|Model[]
	 */
	public function hasMany($table, $link, $key = 'id') {
	    if ($table instanceof Model) {
	        $table = $table->className();
        }
        if (!array_key_exists($table, $this->relations)) {
            $this->setRelation($table, $this->getRelationQuery($table)
                ->where($this->getRelationWhere($link, $key))
                ->all());
        }
        return $this->getRelation($table);
	}

    /**
     * @return bool|int
     */
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

    /**
     * @return Record
     */
	public static function record() {
		return (new Record())->setTable(static::tableName());
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
		return $this->record()->set($addData)->insert();
	}

	public static function batchInsert(array $columns, array $values = null) {
		return static::record()
			->batchInsert($columns, $values);
	}

	/**
	 * 自动获取条件
	 * @return array|bool
	 */
	protected function getWhereKey() {
		foreach ($this->primaryKey as $item) {
			if ($this->has($item)) {
				return [$item => $this->get($item)];
			}
		}
		return false;
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
		$row = $this->record()
            ->set($this->getUpdateData())
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
		return $this->record()
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
		return $this->record()
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
	 * @return static|boolean
     */
	public static function findOne($param, $field = '*', $parameters = array()) {
		$model = new static;
		if (is_numeric($param)) {
			$param = [$model->primaryKey[0] => $param];
		}
		if (!is_array($param) || !array_key_exists('where', $param)) {
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
		return $this->record()
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
		return (new Query())
            ->setModelName(static::className())
            ->from(static::tableName());
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
		return static::find()
			->load($param)
			->select($field)
			->addParam($parameters)
			->all();
	}

    /**
     * @param $args
     * @param int $size
     * @param string $key
     * @param array $parameters
     * @return Page
     */
    public static function findPage($args = null,
                                    $size = 20,
                                    $key = 'page',
                                    $parameters = array()) {
        if (!empty($args) && (!is_array($args) ||
            (!array_key_exists('where', $args) &&
                !array_key_exists('group', $args) &&
                !array_key_exists('order', $args) &&
                !array_key_exists('having', $args))) ) {
            $args = array(
                'where' => $args
            );
        }
        $page = static::find()
            ->addParam($parameters)
            ->load($args)->page($size, $key);
        $data = [];
        foreach ($page->getPage() as $item) {
            $model = new static;
            $model->set($item);
            $model->isNewRecord = false;
            $args[] = $model;
        }
        $page->setPage($data);
        return $page;
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
	public static function count(
	    array $param = array(),
        $field = 'id',
        $parameters = array()) {
		if (!array_key_exists('where', $param)) {
			$param = array(
				'where' => $param
			);
		}
		return static::find()->load($param)
			->addParam($parameters)
			->count($field)->limit(1)->scalar();
	}

    /**
     * Get a specified relationship.
     *
     * @param  string  $relation
     * @return mixed
     */
    public function getRelation($relation) {
        return $this->relations[$relation];
    }

    /**
     * Set the specific relationship in the model.
     *
     * @param  string  $relation
     * @param  mixed  $value
     * @return $this
     */
    public function setRelation($relation, $value) {
        $this->relations[$relation] = $value;
        return $this;
    }
}