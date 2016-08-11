<?php
namespace Zodream\Infrastructure\Database\Schema;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/11
 * Time: 14:50
 */
class Table implements \ArrayAccess {

    const MyISAM = 'MyISAM';
    const HEAP = 'HEAP';
    const MEMORY = 'MEMORY';
    const MERGE = 'MERGE';
    const MRG_MYISAM = 'MRG_MYISAM';
    const InnoDB = 'InnoDB';
    const INNOBASE = 'INNOBASE';

    protected $tableName;

    protected $data = [];

    protected $charset = 'UTF8';

    protected $engine = 'MyIAM';

    public function __construct($table, $data = [], $engine = self::MyISAM, $charset = 'UTF8') {
        $this->tableName = $table;
        $this->data = $data;
        $this->engine = $engine;
        $this->charset = $charset;
    }

    public function setCharset($arg) {
        $this->charset = $arg;
        return $this;
    }

    public function setEngine($arg) {
        $this->engine = $arg;
        return $this;
    }

    public function getName() {
        return $this->tableName;
    }

    public function delete() {
        return 'DROP TABLE IF EXISTS '.$this->tableName;
    }

    public function create() {
        return 'CREATE DATABASE IF NOT EXISTS '.$this->tableName;
    }

    public function replace() {

    }

    public function clear() {
        return 'TRUNCATE '.$this->tableName;
    }

    public function alert() {
        return 'ALTER TABLE '.$this->tableName.' CHANGE COLUMN ';
    }


    /**
     * @param $offset
     * @return bool|Column
     */
    public function get($offset) {
        if (!$this->has($offset)) {
            return false;
        }
        return $this->data[$offset];
    }

    public function has($offset) {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @param $offset
     * @param $column
     * @return Column
     */
    public function set($offset, $column = null) {
        if (!$column instanceof Column) {
            $column = new Column($offset);
        }
        return $this->data[$offset] = $column;
    }


    public function offsetExists($offset) {
        return $this->has($offset);
    }

    public function offsetGet($offset) {
        $this->get($offset);
    }

    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }
}