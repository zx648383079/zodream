<?php
namespace Zodream\Infrastructure\Database\Schema;


/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/25
 * Time: 9:19
 */
use Zodream\Infrastructure\Database\Command;

class Schema {
    /**
     * @var Command
     */
    private $_command;

    protected $schema = 'zodream';

    protected $charset = 'UTF8';

    public function __construct($schema = null) {
        $this->setSchema($schema);
    }

    /**
     * @return Command
     */
    protected function command() {
        if (!$this->_command instanceof Command) {
            $this->_command = Command::getInstance();
        }
        return $this->_command;
    }

    public function setSchema($schema = null) {
        if (empty($schema)) {
            $schema = $this->command()->getEngine()->getConfig('database');
        }
        $this->schema = $schema;
        return $this;
    }

    public function setCharset($charset = 'UTF8') {
        $this->charset = $charset;
        return $this;
    }

    public function create() {
        return $this->command()
            ->execute('CREATE SCHEMA IF NOT EXISTS `'.$this->schema.'` DEFAULT CHARACTER SET '.$this->charset);
    }

    public function update() {
        return $this->command()
            ->execute('ALTER SCHEMA `'.$this->schema.'` DEFAULT COLLATE '.$this->charset);
    }

    public function delete() {
        return $this->command()
            ->execute('DROP DATABASE `'.$this->schema.'`');
    }

    public function clear() {
        $tables = $this->getAllTable();
        return $this->command()->execute('DROP TABLE `'.implode('`,`', $tables).'`');
    }

    /**
     * 获取所有数据库名
     * @return array
     */
    public static function getAllDatabase() {
        return Command::getInstance()->getArray('SHOW DATABASES');
    }

    /**
     * 获取表名
     * @param bool $hasStatus
     * @return array
     */
    public function getAllTable($hasStatus = false) {
        $this->command()
            ->changedDatabase($this->schema);
        if ($hasStatus) {
            return $this->command()
                ->getArray('SHOW TABLE STATUS');
        }
        $tables = $this->command()
            ->getArray('SHOW TABLES');
        foreach ($tables as &$table) {
            $table = current($table);
        }
        return $tables;
    }

    public function table($name) {
        $name = $this->command()->addPrefix($name);
        return new Table("`{$this->schema}`.{$name}");
    }

    /**
     * 合并多个表， 请保证没有重复字段
     * @param string $table
     * @param string|Query $sql
     * @return mixed
     */
    public function mergeTable($table, $sql) {
        return $this->command()->execute('CREATE TABLE '.$this->addPrefix($table).' AS '.$sql);
    }
}