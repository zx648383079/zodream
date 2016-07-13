<?php
namespace Zodream\Domain\Generate;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/17
 * Time: 22:49
 */
/**
SHOW DATABASES                                //列出 MySQL Server 数据库。
SHOW TABLES [FROM db_name]                    //列出数据库数据表。
SHOW CREATE TABLES tbl_name                    //导出数据表结构。
SHOW TABLE STATUS [FROM db_name]              //列出数据表及表状态信息。
SHOW COLUMNS FROM tbl_name [FROM db_name]     //列出资料表字段
SHOW FIELDS FROM tbl_name [FROM db_name]，DESCRIBE tbl_name [col_name]。
SHOW FULL COLUMNS FROM tbl_name [FROM db_name]//列出字段及详情
SHOW FULL FIELDS FROM tbl_name [FROM db_name] //列出字段完整属性
SHOW INDEX FROM tbl_name [FROM db_name]       //列出表索引。
SHOW STATUS                                  //列出 DB Server 状态。
SHOW VARIABLES                               //列出 MySQL 系统环境变量。
SHOW PROCESSLIST                             //列出执行命令。
SHOW GRANTS FOR user                         //列出某用户权限
 */
use Zodream\Domain\Model\Model;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Database\Command;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

class GenerateModel extends Command {

    /**
     * 获取表前缀
     * @return string
     */
    public function getPrefix() {
        return $this->prefix;
    }

    /**
     * 新建数据库
     * @param string $db
     */
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
     * 获取所有数据库名
     */
    public function getDatabase() {
        return $this->_getArrayFormDouble($this->db->getArray('SHOW DATABASES'));
    }

    /**
     * 获取表名
     * @param string $arg 数据库名 默认是配置文件中的数据库
     * @return array
     */
    public function getTableByDatabase($arg = null) {
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
     * @param string $arg 表名
     * @param bool $prefix 是否自动添加前缀
     * @return
     */
    public function getColumn($arg, $prefix = true) {
        if ($prefix) {
            $arg = $this->prefix.StringExpand::firstReplace($arg, $this->prefix);
        }
        return $this->db->getArray('SHOW COLUMNS FROM '.$arg);
    }

    /**
     * 获取表所有列的完整信息
     * @param $arg
     * @return array
     */
    public function getFullColumn($arg) {
        return $this->db->getArray('SHOW FULL COLUMNS FROM '.$arg);
    }

    /**
     * 设置表前缀
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix = null) {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 获取数据库下的所有表的属性
     * @param string $arg
     * @return mixed
     */
    public function getTableStatus($arg = null) {
        if (!empty($arg)) {
            $this->db->execute('use '.$arg);
        }
        return $this->db->getArray('SHOW TABLE STATUS');
    }

    /**
     * 系统生成的创建表的语句
     * @param string $table
     * @return string
     */
    public function getCreateTableSql($table) {
        $data = $this->db->getArray("SHOW CREATE TABLE `{$table}`");
        if (empty($data)) {
            return null;
        }
        $sql = $data[0]['Create Table'];
        return str_replace(
            'CREATE TABLE', 
            "--创建表开始\r\nCREATE TABLE IF NOT EXISTS", 
            $sql). ";\r\n--创建表结束\r\n\r\n";
    }

    /**
     * 获取创造表的格式
     * @param array $arg
     * @return string
     */
    public function getCreateTable(array $arg) {
        $sql = "--创建表开始\r\nCREATE TABLE IF NOT EXISTS `{$arg['Name']}` (\r\n";
        $columns = array();
        foreach ($this->getFullColumn($arg['Name']) as $column) {
            $columns[] = $this->getCreateColumn($column);
        }
        $sql .= implode(",\r\n", $columns). ")\r\nENGINE={$arg['Engine']} DEFAULT CHARSET=utf8";
        if (!empty($arg['Comment'])) {
            $sql .= " COMMENT '{$arg['Comment']}'";
        }
        $sql .= ";\r\n--创建表结束\r\n\r\n";
        return $sql;
    }

    /**
     * 获取表中一列的语句
     * @param array $args
     * @return string
     */
    public function getCreateColumn(array $args) {
        $sql = "    `{$args['Field']}` {$args['Type']}";
        if ($args['Null'] == 'NO') {
            $sql .= ' NOT NULL';
        }
        switch ($args['Key']) {
            case 'PRI':
                $sql .= ' PRIMARY KEY';
                break;
            case 'UNI':
                $sql .= ' UNIQUE';
                break;
            default:
                break;
        }
        if (!is_null($args['Default'])) {
            $sql .= ' DEFAULT '.$args['Default'];
        }
        if (!empty($args['Extra'])) {
            $sql .= ' '.$args['Extra'];
        }
        if (!empty($args['Comment'])) {
            $sql .= " COMMENT '{$args['Comment']}'";
        }
        return $sql;
    }

    /**
     * 获取插入语句
     * @param array $data
     * @param $table
     * @return string
     */
    public function getInsert(array $data, $table) {
        if (empty($data)) {
            return null;
        }
        $sql = "INSERT INTO `{$table}` (`".implode('`, `', array_keys($data[0]))."`) VALUES \r\n";
        foreach ($data as $item) {
            $sql .= "('".implode("', '", array_values($item))."'),\r\n";
        }
        return substr($sql, 0, -3).";\r\n";
    }

    /**
     * 获取连接
     * @return \Zodream\infrastructure\Database\Database
     */
    public function getConnect() {
        return $this->db;
    }
}