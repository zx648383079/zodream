<?php
namespace Zodream\Domain\Generate;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/17
 * Time: 22:49
 */
use Zodream\Domain\Model;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

class GenerateModel extends Model {

    public function getPrefix() {
        return $this->prefix;
    }
    
    public function createDatabase($db) {
        $this->db->execute('CREATE SCHEMA IF NOT EXISTS `'.$db.
            '` DEFAULT CHARACTER SET utf8 ;USE `'.$db.'` ;');
        echo $db.'数据库创建成功！';
    }

    /**
     * 根据文件路径导入数据库
     * @param string $file
     * @param null $db
     */
    public function importSql($file, $db = null) {
        if (!is_file($file)) {
            return;
        }
        if (!empty($db)) {
            $this->createDatabase($db);
        }
        $tables = $this->getTables(file_get_contents($file));
        $charset = Config::getValue('db.encoding', 'utf8');
        foreach ($tables as $key => $value) {
            $this->db->execute(
                "CREATE TABLE IF NOT EXISTS `{$key}` {$value[0]} ENGINE={$value[1]} DEFAULT CHARSET={$charset};"
            );
            echo $key, '表创建成功！<br>';
        }
        echo 'SQL文件执行完成！';
    }

    private function getTables($sql) {
        preg_match_all(
            '/CREATE TABLE[^()]+`([\w_]+)`\s*(\([^;]+\))(\s*?ENGINE\s?=\s?(\w+).*?)?;/i',
            $sql, $matches, PREG_SET_ORDER);
        $result = array();
        foreach ($matches as $item) {
            $table = $this->addPrefix($item[1]);
            $result[$table] = array(
                $item[2],
                strtolower($item[4]) == 'merge' ? $item[4] : 'MYISAM'
            );
        }
        return $result;
    }

    /**
     * 获取数据库名
     */
    public function getDatabase() {
        return $this->_getArrayFormDouble($this->db->getArray('SHOW DATABASES'));
    }

    /**
     * 获取表明
     * @param string $arg 数据库名 默认是配置文件中的数据库
     * @return array
     */
    public function getTable($arg = null) {
        if (!empty($arg)) {
            $this->db->execute('use '.$arg);
        }
        return $this->_getArrayFormDouble($this->db->getArray('SHOW TABLES'));
    }

    private function _getArrayFormDouble(array $args) {
        $result = array();
        foreach ($args as $value) {
            if(!is_array($value)) {
                continue;
            }
            foreach ($value as $val) {
                $result[] = $val;
            }
        }
        return $result;
    }

    /**
     * 获取列名
     * @param string $arg
     */
    public function getColumn($arg) {
        $arg = $this->prefix.StringExpand::firstReplace($arg, $this->prefix);
        return $this->db->getArray('SHOW COLUMNS FROM '.$arg);
    }
}