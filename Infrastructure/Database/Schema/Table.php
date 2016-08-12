<?php
namespace Zodream\Infrastructure\Database\Schema;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/11
 * Time: 14:50
 */
use Zodream\Infrastructure\Database\BaseQuery;

class Table extends BaseQuery {

    const MyISAM = 'MyISAM';
    const HEAP = 'HEAP';
    const MEMORY = 'MEMORY';
    const MERGE = 'MERGE';
    const MRG_MYISAM = 'MRG_MYISAM';
    const InnoDB = 'InnoDB';
    const INNOBASE = 'INNOBASE';

    protected $tableName;

    protected $charset = 'UTF8';

    protected $engine = 'MyIAM';

    protected $foreignKey = [];

    protected $checks = [];

    protected $aiBegin = 1;

    protected $index = [];

    protected $primaryKey;

    protected $comment = null;

    public function __construct($table, $data = [], $engine = self::MyISAM, $charset = 'UTF8') {
        $this->tableName = $this->addPrefix($table);
        $this->_data = $data;
        $this->engine = $engine;
        $this->charset = $charset;
    }

    /**
     * TABLE CHARSET, DEFAULT UTF8
     * @param string $arg
     * @return $this
     */
    public function setCharset($arg) {
        $this->charset = $arg;
        return $this;
    }

    /**
     * TABLE COMMENT
     * @param string $arg
     * @return $this
     */
    public function setComment($arg) {
        $this->comment = $arg;
        return $this;
    }

    /**
     * SET PRIMARY KEY
     * @param string $field
     * @return $this
     */
    public function pk($field) {
        $this->primaryKey = $field;
        return $this;
    }

    /**
     * SET TABLE ENGINE
     * @param string $arg
     * @return $this
     */
    public function setEngine($arg) {
        $this->engine = $arg;
        return $this;
    }

    /**
     * SET AUTO_INCREMENT BEGIN
     * @param string $arg
     * @return $this
     */
    public function setAI($arg) {
        $this->aiBegin = max($this->aiBegin, intval($arg));
        return $this;
    }

    /**
     * SET FOREIGN KEY
     * @param string $name
     * @param string $field
     * @param string $table
     * @param string $fkField
     * @param string $delete
     * @param string $update
     * @return $this
     */
    public function fk($name, $field, $table, $fkField, $delete = 'NO ACTION', $update = 'NO ACTION') {
        $this->foreignKey[$name] = [$field, $table, $fkField, $delete, $update];
        return $this;
    }

    /**
     * SET INDEX
     * @param string $name
     * @param string $field
     * @param string $order asc or desc
     * @return $this
     */
    public function index($name, $field, $order = null) {
        $this->index[$name] = [$field, $order];
        return $this;
    }

    /**
     * SET UNIQUE
     * @param string $name
     * @param string $field
     * @param sting $order
     * @return $this
     */
    public function unique($name, $field, $order = null) {
        $this->index[$name] = [$field, $order, 'UNIQUE'];
        return $this;
    }


    /**
     * SET CHECK
     * @param string $name
     * @param string $arg
     * @return $this
     */
    public function check($name, $arg = null) {
        if (empty($arg)) {
            $this->checks[] = $name;
        } else {
            $this->checks[$name] = $arg;
        }
        return $this;
    }

    /**
     * GET TABLE NAME
     * @return string
     */
    public function getName() {
        return $this->tableName;
    }

    /**
     * DROP TABLE
     * @return mixed
     */
    public function drop() {
        return $this->command()->execute($this->getDropSql());
    }

    /**
     * CREATE TABLE
     * @return mixed
     */
    public function create() {
        return $this->command()->execute($this->getSql());
    }

    /**
     * DROP AND CREATE TABLE
     * @return mixed
     */
    public function replace() {
        $this->drop();
        return $this->create();
    }

    /**
     * TRUNCATE TABLE
     * @return mixed
     */
    public function truncate() {
        return $this->command()->execute($this->getTruncateSql());
    }

    /**
     * ALERT TABLE
     * @return mixed
     */
    public function alert() {
        return $this->command()->execute($this->getTruncateSql());
    }


    /**
     * @param $offset
     * @return bool|Column
     */
    public function get($offset) {
        if (!$this->has($offset)) {
            return false;
        }
        return $this->_data[$offset];
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
        return $this->_data[$offset] = $column;
    }

    /**
     * GET DROP AND CREATE TABLE SQL
     * @return string
     */
    public function getReplaceSql() {
        return $this->getDropSql().$this->getSql();
    }

    /**
     * GET TRUNCATE TABLE SQL
     * @return string
     */
    public function getTruncateSql() {
        return "TRUNCATE `{$this->tableName}`;";
    }

    /**
     * GET ALERT TABLE SQL
     * @return string
     */
    public function getAlertSql() {
        $sql = [];
        foreach ($this->_data as $item) {
            $sql[] = $item->getAlterSql();
        }
        return "ALTER TABLE `$this->tableName` ".implode(',', $sql).';';
    }

    /**
     * GET DROP TABLE SQL
     * @return string
     */
    public function getDropSql() {
        return "DROP TABLE IF EXISTS `{$this->tableName}`;";
    }

    /**
     * GET CREATE TABLE SQL
     * @return string
     */
    public function getSql() {
        $sql = "CREATE DATABASE IF NOT EXISTS `{$this->tableName}` (";
        $column = $this->_data;
        if (!empty($this->primaryKey)) {
            $column[] = "PRIMARY KEY (`{$this->primaryKey}`)";
        }
        foreach ($this->checks as $key => $item) {
            $column[] = (!is_integer($key) ? "CONSTRAINT `{$key}` " : null)." CHECK ({$item})";
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
}