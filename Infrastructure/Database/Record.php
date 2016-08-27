<?php
namespace Zodream\Infrastructure\Database;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/12
 * Time: 19:24
 */
use Zodream\Infrastructure\ObjectExpand\StringExpand;

class Record extends Query {
    
    protected $parameters = [];
    
    public function addParam($key, $value = null) {
        if (is_object($key)) {
            $key = (array)$key;
        }
        if (is_array($key)) {
            $this->parameters = array_merge($this->parameters, $key);
            return $this;
        }
        if (empty($key)) {
            return $this;
        }
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * @param array|string $key 需要添加的集合
     * @param string $value
     * @return static
     */
    public function set($key, $value = null) {
        if (is_null($key) && !is_null($value)) {
            $this->_data[] = $value;
            return $this;
        }
        parent::set($key, $value);
        return $this;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function load(array $args) {
        $this->_data = $args;
        return $this;
    }

    public function setTable($table = null) {
        if (is_null($table)) {
            $table = current($this->from);
        }
        $this->command()->setTable($table);
        return $this;
    }


    /**
     * 新增记录
     *
     * @access public
     *
     * @return int 返回最后插入的ID,
     */
    public function insert() {
        $addFields = implode('`,`', array_keys($this->_data));
        return $this->setTable()
            ->command()
            ->insert("`{$addFields}`", StringExpand::repeat('?', count($this->_data)),
                array_values($this->_data));
    }

    /**
     * INSERT MANY RECORDS
     * @param array|string $columns
     * @param array $data
     * @return int
     */
    public function batchInsert($columns, array $data) {
        $args = [];
        foreach ($data as $item) {
            $arg = [];
            foreach ($item as $value) {
                if (is_null($value)) {
                    $arg[] = 'NULL';
                    continue;
                }
                if (is_bool($value)) {
                    $arg[] = intval($value);
                    continue;
                }
                if (is_string($value)) {
                    $arg[] = "'".addslashes($value)."'";
                    continue;
                }
                if (is_array($value) || is_object($value)) {
                    $arg[] = "'".serialize($value)."'";
                    continue;
                }
                $arg[] = $value;
            }
            $args[] = '(' . implode(', ', $arg) . ')';
        }

        return $this->setTable()
            ->command()
            ->insert(implode(', ', (array)$columns), implode(', ', $args));
    }

    public function update() {
        $data = [];
        $parameters = array();
        foreach ($this->_data as $key => $value) {
            if (is_integer($key)) {
                $data[] = $value;
                continue;
            }
            $data[] = "`{$key}` = ?";
            $parameters[] = $value;
        }
        return $this->setTable()
            ->command()
            ->update(implode(',', $data), $this->getWhere().$this->getLimit(), $parameters);
    }

    public function replace() {
        $addFields = implode('`,`', array_keys($this->_data));
        return $this->setTable()
            ->command()
            ->insertOrReplace("`{$addFields}`", StringExpand::repeat('?', count($this->_data)),
                array_values($this->_data));
    }
    
    public function delete($tag = null) {
        if (func_num_args() > 0) {
            return call_user_func_array('parent::delete', func_get_args());
        }
        return $this->setTable()
            ->command()
            ->delete($this->getWhere().$this->getLimit(), $this->parameters);
    }
}