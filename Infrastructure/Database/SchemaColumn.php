<?php
namespace Zodream\Infrastructure\Database;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/25
 * Time: 10:53
 */
class SchemaColumn {
    protected $data = [];

    protected $name = null;

    public function name($key) {
        $this->name = $key;
        return $this;
    }

    protected function addData($arg) {
        $data[] = $arg;
        return $this;
    }

    public function null() {
        return $this->addData('NULL');
    }

    public function notNull() {
        return $this->addData('NOT NULL');
    }

    public function int($arg = null) {
        $sql = 'INT';
        if (!empty($arg)) {
            $sql .= '('.intval($arg).')';
        }
        return $this->addData($sql);
    }

    public function tinyint($arg = 1) {
        return $this->addData('TINYINT('.intval($arg).')');
    }

    public function bool() {
        return $this->tinyint(1);
    }

    public function comment($arg) {
        return $this->addData("COMMENT '{$arg}'");
    }

    public function char($arg) {
        return $this->addData('CHAR('.intval($arg).')');
    }

    public function varchar($arg = 255) {
        return $this->addData('VARCHAR('.intval($arg).')');
    }

    public function text() {
        return $this->addData('TEXT');
    }

    public function default($arg) {
        if (is_string($arg)) {
            $arg = "'{$arg}'";
        }
        return $this->addData('DEFAULT '.$arg);
    }

    public function enum(array $args) {
        return $this->addData('ENUM(\''.implode("', '", $args)."')");
    }

    public function auto() {
        return $this->addData('AUTO_INCREMENT');
    }

    public function pk() {
        return $this-$this->addData('PRIMARY KEY');
    }

    public function __toString() {
        $sql = implode(' ', $this->data);
        if (!empty($this->name)) {
            $sql = "`{$this->name}` ".$sql;
        }
        return $sql;
    }
}