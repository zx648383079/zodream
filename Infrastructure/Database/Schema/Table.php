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

    /**
     * @var Column[]
     */
    protected $data = [];

    protected $charset = 'UTF8';

    protected $engine = 'MyIAM';

    protected $foreignKey = [];

    protected $checks = [];

    protected $aiBegin = 1;

    protected $index = [];

    protected $primaryKey;

    protected $comment = null;

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

    public function setComment($arg) {
        $this->comment = $arg;
        return $this;
    }

    public function pk($field) {
        $this->primaryKey = $field;
        return $this;
    }

    public function setEngine($arg) {
        $this->engine = $arg;
        return $this;
    }

    public function setAI($arg) {
        $this->aiBegin = max($this->aiBegin, intval($arg));
        return $this;
    }

    public function fk($name, $field, $table, $fkField, $delete = 'NO ACTION', $update = 'NO ACTION') {
        $this->foreignKey[$name] = [$field, $table, $fkField, $delete, $update];
        return $this;
    }

    public function index($name, $field, $order = null) {
        $this->index[$name] = [$field, $order];
        return $this;
    }

    public function unique($name, $field, $order = null) {
        $this->index[$name] = [$field, $order, 'UNIQUE'];
        return $this;
    }


    public function check($name, $arg) {
        $this->checks[$name] = $arg;
        return $this;
    }

    public function getName() {
        return $this->tableName;
    }

    public function delete() {
        return "DROP TABLE IF EXISTS `{$this->tableName}`;";
    }

    public function create() {
        $sql = "CREATE DATABASE IF NOT EXISTS `{$this->tableName}` (";
        $column = $this->data;
        if (!empty($this->primaryKey)) {
            $column[] = "PRIMARY KEY (`{$this->primaryKey}`)";
        }
        foreach ($this->index as $key => $item) {
            $column[] = (count($item) > 2 ? 'UNIQUE ': null). "INDEX `{$key}` (`{$item[0]}` {$item['1']})";
        }

        foreach ($this->foreignKey as $key => $item) {
            $column[] = "CONSTRAINT `{$key}` FOREIGN KEY (`{$item[0]}`) REFERENCES `{$item[1]}` (`{$item[2]}`) ON DELETE {$item[2]} ON UPDATE {$item[3]}";
        }
        $sql .= implode(',', $column).") ENGINE={$this->engine}";
        if ($this->aiBegin > 1) {
            $sql .= ' AUTO_INCREMENT='.$this->aiBegin;
        }
        return $sql." DEFAULT CHARSET={$this->charset} COMMENT={$this->comment};";
    }

    public function replace() {
        return $this->delete().$this->create();
    }

    public function clear() {
        return "TRUNCATE `{$this->tableName}`;";
    }

    public function alert() {
        $sql = [];
        foreach ($this->data as $item) {
            $sql[] = $item->getAlterSql();
        }
        return "ALTER TABLE `$this->tableName` ".implode(',', $sql).';';
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