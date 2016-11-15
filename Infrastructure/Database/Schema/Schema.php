<?php
namespace Zodream\Infrastructure\Database\Schema;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/25
 * Time: 9:19
 */
use Zodream\Infrastructure\Database\Query\BaseQuery;

class Schema extends BaseQuery {
    
    public function addParam($params) {
        
    }

    /**
     * 数据库
     * @param string $name
     * @param string $type CREATE or DROP
     * @return mixed
     */
    public function database($name, $type = 'CREATE') {
        return $this->command()->execute(strtoupper($type).' DATABASE '.$name);
    }

    
    public function createDatabase($name, $charset = 'UTF8') {
        return $this->command()
            ->execute('CREATE DATABASE IF NOT EXISTS '.$name.' DEFAULT CHARACTER SET '.$charset);
    }

    /**
     * 创建表
     * @param string $name
     * @param string $engine
     * @return mixed
     */
    public function createTable($name, $engine = 'MYISAM') {
        return $this->command()->execute('CREATE TABLE IF NOT EXISTS '.$this->addPrefix($name).
            ' ('. $this->getColumns(). ') ENGINE = '.$engine.' DEFAULT CHARSET=UTF8;');
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

    /**
     * 获取所有数据库名
     */
    public function getAllDatabase() {
        return $this->command()->getArray('SHOW DATABASES');
    }

    /**
     * 获取表名
     * @param string $arg 数据库名 默认是配置文件中的数据库
     * @return array
     */
    public function getAllTable($arg = null) {
        if (!empty($arg)) {
            $this->command()->changedDatabase($arg);
        }
        return $this->command()->getArray('SHOW TABLES');
    }

    protected function getColumns() {
        return '';
    }

    public function getSql() {
        return '';
    }

    public function __toString() {
        return $this->getSql();
    }
}