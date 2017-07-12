<?php
namespace Zodream\Domain\Model;

use Zodream\Infrastructure\Database\Query\Query as BaseQuery;

class Query extends BaseQuery {

    protected $relations = [];

    protected $modelName;
    protected $isArray = false;

    public function with($relations) {
        if (!is_array($relations)) {
            $relations = func_get_args();
        }
        $this->relations = array_merge($this->relations, $relations);
        return $this;
    }

    public function setModelName($model) {
        if ($model instanceof Model) {
            $model = $model->className();
        }
        $this->modelName = $model;
        return $this;
    }

    public function asArray() {
        $this->isArray = true;
        return $this;
    }

    /**
     * @param bool $isArray
     * @return array|object[]|Model[]
     */
    public function all($isArray = true) {
        $data = parent::all($isArray);
        if (empty($data)
            || $this->isArray
            || !$isArray
            || !class_exists($this->modelName)) {
            return $data;
        }
        $args = [];
        foreach ($data as $item) {
            /** @var $model Model */
            $model = new $this->modelName;
            $model->set($item);
            $model->isNewRecord = false;
            $args[] = $model;
        }
        return $args;
    }

    /**
     * 取一个值
     * @return bool|int|string
     */
    public function scalar() {
        $this->asArray();
        return parent::scalar();
    }

    /**
     * 更新
     * @param array $args
     * @return int
     */
    public function update(array $args) {
        $data = [];
        foreach ($args as $key => $value) {
            if (is_integer($key)) {
                $data[] = $value;
                continue;
            }
            $data[] = "`{$key}` = ?";
            $this->addParam($value);
        }
        return $this->command()
            ->update(implode(',', $data), $this->getWhere().$this->getLimit(), $this->get());
    }

    /**
     * 删除
     * @return int
     */
    public function delete() {
        return $this->command()
            ->delete($this->getWhere().$this->getLimit(), $this->get());
    }
}