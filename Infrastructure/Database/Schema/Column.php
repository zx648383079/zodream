<?php
namespace Zodream\Infrastructure\Database\Schema;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/25
 * Time: 10:53
 */
class Column {

    const KIND = 0;

    const NULL = 1;

    const PK = 2;

    const UQ = 3;

    const AI = 4;

    const DEFAULT = 5;

    const COMMENT = 6;

    protected $data = [];

    protected $field;

    protected $oldField;

    protected $foreignKey = [];

    protected $checks = [];

    protected $aiBegin = null;

    public function __construct($field) {
        $this->setField($field);
    }

    public function setField($field) {
        if (is_array($field)) {
            list($this->oldField, $this->field) = $field;
        } else {
            $this->oldField = $this->field = $field;
        }
        return $this;
    }

    public function setOldField($field) {
        $this->oldField = $field;
        return $this;
    }

    public function getOldField() {
        return $this->oldField;
    }

    public function getField() {
        return $this->field;
    }

    protected function addData($index, $arg) {
        $data[$index] = $arg;
        return $this;
    }

    public function null() {
        return $this->addData(self::NULL, 'NULL');
    }

    public function notNull() {
        return $this->addData(self::NULL, 'NOT NULL');
    }

    public function smallInt($arg) {
        return $this->addData(self::KIND, 'SMALLINT('.intval($arg).')');
    }

    public function bigInt($arg) {
        return $this->addData(self::KIND, 'BIGINT('.intval($arg).')');
    }

    public function float($size, $d) {
        return $this->addData(self::KIND, 'FLOAT('.intval($size).','.intval($d).')');
    }

    public function double($size, $d) {
        return $this->addData(self::KIND, 'DOUBLE('.intval($size).','.intval($d).')');
    }

    public function decimal($size, $d) {
        return $this->addData(self::KIND, 'DECIMAL('.intval($size).','.intval($d).')');
    }

    public function int($arg = null) {
        $sql = 'INT';
        if (!empty($arg)) {
            $sql .= '('.intval($arg).')';
        }
        return $this->addData(self::KIND, $sql);
    }

    public function tinyint($arg = 1) {
        return $this->addData(self::KIND, 'TINYINT('.intval($arg).')');
    }

    public function bool() {
        return $this->tinyint(1);
    }

    public function char($arg) {
        return $this->addData(self::KIND, 'CHAR('.intval($arg).')');
    }

    public function varchar($arg = 255) {
        return $this->addData(self::KIND, 'VARCHAR('.min(255, intval($arg)).')');
    }

    /***
     * 65535
     * @return Column
     */
    public function text() {
        return $this->addData(self::KIND, 'TEXT');
    }

    /**
     * 16,777,215
     * @return Column
     */
    public function mediumtext() {
        return $this->addData(self::KIND, 'MEDIUMTEXT');
    }

    /**
     * 4,294,967,295
     * @return Column
     */
    public function longtext() {
        return $this->addData(self::KIND, 'LONGTEXT');
    }

    public function blob() {
        return $this->addData(self::KIND, 'BLOB');
    }

    public function mediumblob() {
        return $this->addData(self::KIND, 'MEDIUMBLOB');
    }

    public function longblob() {
        return $this->addData(self::KIND, 'LONGBLOB');
    }

    public function dateTime() {
        return $this-$this->addData(self::KIND, 'DATETIME');
    }

    public function date() {
        return $this-$this->addData(self::KIND, 'DATE');
    }

    public function time() {
        return $this-$this->addData(self::KIND, 'TIME');
    }

    public function year() {
        return $this-$this->addData(self::KIND, 'YEAR');
    }

    public function enum(array $args) {
        return $this->addData(self::DEFAULT, 'ENUM(\''.implode("', '", $args)."')");
    }


    public function default($arg) {
        if (is_string($arg)) {
            $arg = "'{$arg}'";
        }
        return $this->addData(self::DEFAULT, 'DEFAULT '.$arg);
    }

    public function comment($arg) {
        return $this->addData(self::COMMENT, "COMMENT '{$arg}'");
    }

    public function ai($begin = null) {
        $this->aiBegin = $begin;
        return $this->addData(self::AI, 'AUTO_INCREMENT');
    }

    public function pk() {
        return $this-$this->addData(self::PK, 'PRIMARY KEY');
    }



    public function fk($name, $table, $field) {
        $this->foreignKey[$name] = [$table, $field];
        return $this;
    }

    public function check($name, $arg) {
        $this->checks[$name] = $arg;
        return $this;
    }

    public function __toString() {
        $sql = implode(' ', $this->data);
        if (!empty($this->name)) {
            $sql = "`{$this->name}` ".$sql;
        }
        return $sql;
    }
}