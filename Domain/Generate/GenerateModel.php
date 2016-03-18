<?php
namespace Zodream\Domain\Generate;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/17
 * Time: 22:49
 */
use Zodream\Domain\Model;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

class GenerateModel extends Model {

    public function getPrefix() {
        return $this->prefix;
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
            $this->db->execute('CREATE SCHEMA IF NOT EXISTS `'.$db.
                '` DEFAULT CHARACTER SET utf8 ;USE `'.$db.'` ;');
            echo $db.'数据库创建成功！';
        }
        $content = file_get_contents($file);
        $sqls = explode(";\n", str_replace("\r", "\n", $content));
        foreach ($sqls as $sql) {
            $this->db->execute($sql);
            $match = array();
            if (preg_match('/create[^(;]+ table ([\w_]+?) \(/i', $sql, $match)) {
                echo $match[1].' 表创建成功！<br>';
            }
        }
        echo 'SQL文件执行完成！';
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