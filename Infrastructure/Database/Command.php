<?php
namespace Zodream\Infrastructure\Database;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/5/14
 * Time: 9:07
 */
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Database\Engine\BaseEngine;
use Zodream\Infrastructure\Database\Engine\Pdo;
use Zodream\Infrastructure\Event\EventManger;
use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Traits\SingletonPattern;

class Command {

    use SingletonPattern;

    protected $table;

    protected $prefix;

    protected $allowCache = true;

    protected $cacheLife = 3600;

    /**
     * @var BaseEngine[]
     */
    protected $engines = [];

    protected $currentName = '__default';

    protected $configs = [];

    public function __construct() {
        Factory::timer()->record('dbInit');
        $configs = Config::getInstance()->get('db');
        if (!is_array(current($configs))) {
            $configs = [
                $this->currentName => $configs
            ];
        }
        if (!array_key_exists($this->currentName, $configs)) {
            $this->currentName = key($configs);
        }
        $this->configs = $configs;
        if (isset($this->table)) {
            $this->setTable($this->table);
        }
    }

    /**
     * @param string $name
     * @param array $configs
     * @return BaseEngine
     */
    public function addEngine($name, $configs) {
        if (array_key_exists($name, $this->engines)) {
            $this->engines[$name]->close();
        }
        if ($configs instanceof BaseEngine) {
            return $this->engines[$name] = $configs;
        }
        if (!array_key_exists('driver', $configs) || !class_exists($configs['driver'])) {
            $configs['driver'] = Pdo::class;
        }
        $class = $configs['driver'];
        $this->engines[$name] = new $class($configs);
        Factory::timer()->record('dbEnd');
        return $this->engines[$name];
    }

    public function getEngine($name = null) {
        Factory::timer()->record('dbGet');
        if (is_null($name)) {
            $name = $this->currentName;
        }
        if (array_key_exists($name, $this->engines)) {
            return $this->engines[$name];
        }
        if (array_key_exists($name, $this->configs)) {
            return $this->addEngine($name, $this->configs[$name]);
        }
        throw new \InvalidArgumentException($name. ' DOES NOT HAVE CONFIG!');
    }

    public function addPrefix($table) {
        if (strpos($table, '!') === 0) {
            return substr($table, 1);
        }
        $prefix = $this->getEngine()->getConfig('prefix');
        if (empty($prefix)) {
            return $table;
        }
        return $prefix.StringExpand::firstReplace($table, $prefix);
    }

    /**
     * 设置表
     * @param string $table
     * @return $this
     */
    public function setTable($table) {
        $this->table = $this->addPrefix($table);
        return $this;
    }

    public function getTable() {
        return $this->table;
    }

    /**
     * 更改数据库
     * @param string $database
     * @return $this
     */
    public function changedDatabase($database) {
        $this->getEngine()->execute('use '.$database);
        return $this;
    }

    /**
     * 拷贝（未实现）
     */
    public function copy() {
        return $this->select(null, '* INTO table in db');
    }

    /**
     * @param string $sql
     * @return array null
     */
    public function getCache($sql) {
        if (!$this->allowCache) {
            return null;
        }
        $cache = Factory::cache()->get('data/'.md5($this->currentName.$sql));
        if (empty($cache)) {
            return null;
        }
        return unserialize($cache);
    }

    public function setCache($sql, $data) {
        if (!$this->allowCache || DEBUG) {
            return;
        }
        return Factory::cache()->set('data/'.md5($this->currentName.$sql), serialize($data), 3600);
    }

    /**
     * 查询
     * @param string $sql
     * @param string $field
     * @param array $parameters
     * @return mixed
     */
    public function select($sql, $field = '*', $parameters = array()) {
        return $this->getArray("SELECT {$field} FROM {$this->table} {$sql}", $parameters);
    }

    /**
     * 执行事务
     * @param array $args sql语句的数组
     * @return bool
     */
    public function transaction($args) {
        return $this->getEngine()->transaction($args);
    }

    /**
     * 开始执行事务
     * @return Database
     */
    public function beginTransaction() {
        $this->getEngine()->begin();
        return $this->getEngine();
    }

    /**
     * 插入
     * @param string $columns
     * @param string $tags
     * @param array $parameters
     * @return int
     */
    public function insert($columns, $tags, $parameters = array()) {
        if (!empty($columns) && strpos($columns, '(') === false) {
            $columns = '('.$columns.')';
        }
        $tags = trim($tags);
        if (strpos($tags, '(') !== 0) {
            $tags = '('.$tags.')';
        }
        return $this->getEngine()->insert("INSERT INTO {$this->table} {$columns} VALUES {$tags}", $parameters);
    }

    /**
     * 如果行作为新记录被insert，则受影响行的值为1；如果原有的记录被更新，则受影响行的值为2。 如果有多条存在则只更新最后一条
     * @param string $columns
     * @param string $tags
     * @param string $update
     * @param array $parameters
     * @return int
     */
    public function insertOrUpdate($columns, $tags, $update, $parameters = array()) {
        if (!empty($columns) && strpos($columns, '(') === false) {
            $columns = '('.$columns.')';
        }
        $tags = trim($tags);
        if (strpos($tags, '(') !== 0) {
            $tags = '('.$tags.')';
        }
        return $this->getEngine()->update("INSERT INTO {$this->table} {$columns} VALUES {$tags} ON DUPLICATE KEY UPDATE {$update}", $parameters);
    }

    /**
     *在执行REPLACE后，系统返回了所影响的行数，如果返回1，说明在表中并没有重复的记录，如果返回2，说明有一条重复记录，系统自动先调用了 DELETE删除这条记录，然后再记录用INSERT来insert这条记录。如果返回的值大于2，那说明有多个唯一索引，有多条记录被删除和insert。
     * @param string $columns
     * @param string $tags
     * @param array $parameters
     * @return int
     */
    public function insertOrReplace($columns, $tags, $parameters = array()) {
        if (!empty($columns) && strpos($columns, '(') === false) {
            $columns = '('.$columns.')';
        }
        $tags = trim($tags);
        if (strpos($tags, '(') !== 0) {
            $tags = '('.$tags.')';
        }
        return $this->getEngine()->update("REPLACE INTO {$this->table} {$columns} VALUES {$tags}", $parameters);
    }

    /**
     * 更新
     * @param string $columns
     * @param string $where
     * @param array $parameters
     * @return int
     */
    public function update($columns, $where, $parameters = array()) {
        if (strncasecmp(ltrim($where), 'where', 5) !== 0) {
            $where = 'WHERE '.$where;
        }
        return $this->getEngine()->update("UPDATE {$this->table} SET {$columns} {$where}", $parameters);
    }

    /**
     * 删除
     * @param string $where
     * @param array $parameters
     * @return int
     */
    public function delete($where = null, $parameters = array()) {
        $where = trim($where);
        if (!empty($where) && strncasecmp($where, 'where', 5) !== 0) {
            $where = 'WHERE '.$where;
        }
        return $this->getEngine()->delete("DELETE FROM {$this->table} {$where}", $parameters);
    }

    /**
     * @param string $sql
     * @param array $parameters
     * @return mixed
     */
    public function execute($sql, $parameters = array()) {
        EventManger::getInstance()->run('executeSql', $sql);
        if (preg_match('/^(insert|delete|update|replace|drop|create)\s+/i', $sql)) {
            return $this->getEngine()->execute($sql, $parameters);
        }
        $args = empty($parameters) ? serialize($parameters) : null;
        if ($cache = $this->getCache($sql.$args)) {
            return $cache;
        }
        $result = $this->getEngine()->execute($sql, $parameters);
        $this->setCache($sql.$args, $result);
        return $result;
    }

    /**
     * @param string $sql
     * @param array $parameters
     * @return array
     */
    public function getArray($sql, $parameters = array()) {
        $args = empty($parameters) ? serialize($parameters) : null;
        if ($cache = $this->getCache($sql.$args)) {
            return $cache;
        }
        $result = $this->getEngine()->getArray($sql, $parameters);
        $this->setCache($sql.$args, $result);
        return $result;
    }
    /**
     * @param string $sql
     * @param array $parameters
     * @return object
     */
    public function getObject($sql, $parameters = array()) {
        $args = empty($parameters) ? serialize($parameters) : null;
        if ($cache = $this->getCache($sql.$args)) {
            return $cache;
        }
        $result = $this->getEngine()->getObject($sql, $parameters);
        $this->setCache($sql.$args, $result);
        return $result;
    }

    /**
     * 获取错误信息
     */
    public function getError() {
        return $this->getEngine()->getError();
    }
}