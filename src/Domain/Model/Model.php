<?php
namespace Zodream\Domain\Model;
/**
 * 数据基类
 *
 * @author Jason
 */
use Zodream\Domain\Html\Page;
use Zodream\Domain\Model\Concerns\AutoModel;
use Zodream\Domain\Model\Concerns\HasAttributes;
use Zodream\Domain\Model\Concerns\HasRelation;
use Zodream\Domain\Model\Concerns\HasTimestamps;
use Zodream\Domain\Model\Concerns\ValidateData;
use Zodream\Infrastructure\Database\Query\Record;
use Zodream\Infrastructure\Base\MagicObject;
use Zodream\Infrastructure\Traits\ErrorTrait;
use Zodream\Infrastructure\Traits\EventTrait;

abstract class Model extends MagicObject {

    use ErrorTrait, AutoModel, EventTrait, HasRelation, HasAttributes, ValidateData, HasTimestamps;

    const BEFORE_SAVE = 'before save';
    const AFTER_SAVE = 'after save';
    const BEFORE_INSERT = 'before insert';
    const AFTER_INSERT = 'after insert';
    const BEFORE_UPDATE = 'before update';
    const AFTER_UPDATE = 'after update';

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    public $isNewRecord = true;




	/**
	 * 标签
	 * @return array
	 */
	protected function labels() {
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

	public function __construct($data = []) {
	    if (!empty($data)) {
	        $this->load($data);
        }
		$this->init();
	}
	
	public function init() {
		
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
		$this->invoke(self::BEFORE_SAVE, [$this]);
		if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }
		if ($this->isNewRecord) {
			$row = $this->insert();
		} else {
			$row = $this->update();
		}
		$this->invoke(self::BEFORE_SAVE, [$this]);
		return $row;
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
     * @return bool|int
     */
	public function insert() {
		if (!$this->validate()) {
			return false;
		}
		$this->invoke(self::BEFORE_INSERT, [$this]);
		$row = static::record()
            ->set($this->getValues())
            ->insert();
		if (!empty($row)) {
			$this->set(current($this->primaryKey), $row);
            $this->setOldData();
		}
		$this->invoke(self::AFTER_INSERT, [$this]);
		return $row;
	}

    /**
     * @return Record
     */
	public static function record() {
		return (new Record())->setTable(static::tableName());
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
	 * @return int 返回影响的行数,
	 */
	public function update() {
		if (!$this->validate()) {
			return false;
		}
		$data = $this->getUpdateData();
		if (empty($data)) {
		    return true;
        }
		$this->invoke(self::BEFORE_UPDATE, [$this]);
		$row = $this->record()
            ->set($data)
            ->whereMany($this->getWhereKey())
			->update();
        if (!empty($row)) {
            $this->setOldData();
        }
		$this->invoke(self::AFTER_UPDATE, [$this]);
		return $row;
	}

    /**
     * 初始化并保存到数据库
     * @param array $data
     * @return static
     */
	public static function create(array $data) {
	    $model = new static($data);
	    $model->save();
	    return $model;
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
	public static function find($param, $field = '*', $parameters = array()) {
	    if (empty($param)) {
	        return false;
        }
		$model = new static;
		if (is_numeric($param)) {
			$param = [$model->primaryKey[0] => $param];
		}
		if (!is_array($param) || !array_key_exists('where', $param)) {
			$param = [
				'where' => $param
			];
		}
		return static::query()
			->load($param)
			->select($field)
			->addParam($parameters)
			->one();
	}

    /**
     * 查找或新增
     * @param $param
     * @param string $field
     * @param array $parameters
     * @return bool|Model|static
     */
	public static function findOrNew($param, $field = '*', $parameters = array()) {
	    $model = static::find($param, $field, $parameters);
	    if (empty($model)) {
	        return new static();
        }
        return $model;
    }

	/**
	 * 删除数据
	 * DELETE QUERY
	 *
	 * @return int 返回影响的行数,
	 */
	public function delete() {
		$row = $this->record()
			->whereMany($this->getWhereKey())
			->delete();
		if (!empty($row)) {
		    $this->initOldData();
        }
        return $row;
	}

	/**
	 * 查询数据
	 *
	 * @access public
	 *
	 * @return Query 返回查询结果,
	 */
	public static function query() {
		return (new Query())
            ->setModelName(static::className())
            ->from(static::tableName());
	}


	public static function __callStatic($method, $arguments) {
		return call_user_func_array([
           	static::query(), $method], $arguments);
    }
}