<?php
namespace Zodream\Infrastructure\Database;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/5/14
 * Time: 9:07
 */
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Traits\SingletonPattern;

class Command {
    
    use SingletonPattern;
    
    protected $table;
    
    protected $prefix;
    /**
     * @var Database
     */
    protected $db;

    public function __construct() {
        $configs = Config::getInstance()->get('db');
        $this->db = call_user_func(array($configs['driver'], 'getInstance'), $configs);
        $this->prefix = $configs['prefix'];
        if (isset($this->table)) {
            $this->setTable($this->table);
        }
    }

    public function addPrefix($table) {
        if (empty($this->prefix)) {
            return $table;
        }
        return $this->prefix. StringExpand::firstReplace($table, $this->prefix, null);
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
        $this->db->execute('use '.$database);
        return $this;
    }

    /**
     * 拷贝（未实现）
     */
    public function copy() {
        return $this->select(null, '* INTO table in db');
    }

    /**
     * 查询
     * @param $sql
     * @param string $field
     * @param array $parameters
     * @return mixed
     */
    public function select($sql, $field = '*', $parameters = array()) {
        return $this->db->getArray("SELECT {$field} FROM {$this->table} {$sql}", $parameters);
    }

    /**
     * 插入
     * @param string $columns
     * @param string $tags
     * @param array $parameters
     * @return int
     */
    public function insert($columns, $tags, $parameters = array()) {
        return $this->db->insert("INSERT INTO {$this->table} ({$columns}) VALUES ({$tags})", $parameters);
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
        return $this->db->update("INSERT INTO {$this->table} ({$columns}) VALUES ({$tags}) ON DUPLICATE KEY UPDATE {$update}", $parameters);
    }

    /**
     *在执行REPLACE后，系统返回了所影响的行数，如果返回1，说明在表中并没有重复的记录，如果返回2，说明有一条重复记录，系统自动先调用了 DELETE删除这条记录，然后再记录用INSERT来insert这条记录。如果返回的值大于2，那说明有多个唯一索引，有多条记录被删除和insert。
     * @param string $columns
     * @param string $tags
     * @param array $parameters
     * @return int
     */
    public function insertOrReplace($columns, $tags, $parameters = array()) {
        return $this->update("REPLACE INTO {$this->table} ({$columns}) VALUES ({$tags})", $parameters);
    }

    /**
     * 更新
     * @param string $columns
     * @param string $where
     * @param array $parameters
     * @return int
     */
    public function update($columns, $where, $parameters = array()) {
        if (strncasecmp($where, 'where', 5) !== 0) {
            $where = 'WHERE '.$where;
        }
        return $this->db->update("UPDATE {$this->table} SET {$columns} {$where}", $parameters);
    }

    /**
     * 删除
     * @param string $where
     * @param array $parameters
     * @return int
     */
    public function delete($where, $parameters = array()) {
        if (strncasecmp($where, 'where', 5) !== 0) {
            $where = 'WHERE '.$where;
        }
        return $this->db->delete("DELETE FROM {$this->table} {$where}", $parameters);
    }

    /**
     * @param string $sql
     * @param array $parameters
     * @return mixed
     */
    public function execute($sql, $parameters = array()) {
        return $this->db->execute($sql, $parameters);
    }
    /**
     * @param string $sql
     * @param array $parameters
     * @return mixed
     */
    public function getArray($sql, $parameters = array()) {
        return $this->db->getArray($sql, $parameters);
    }
    /**
     * @param string $sql
     * @param array $parameters
     * @return mixed
     */
    public function getObject($sql, $parameters = array()) {
        return $this->db->getObject($sql, $parameters);
    }

    /**
     * @return Database
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * 获取错误信息
     */
    public function getError() {
        return $this->db->getError();
    }
}