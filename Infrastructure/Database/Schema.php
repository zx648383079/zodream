<?php
namespace Zodream\Infrastructure\Database;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/25
 * Time: 9:19
 */

class Schema extends BaseQuery {

    const TYPE_PK = 'pk';
    const TYPE_UPK = 'upk';
    const TYPE_BIGPK = 'bigpk';
    const TYPE_UBIGPK = 'ubigpk';
    const TYPE_CHAR = 'char';
    const TYPE_STRING = 'string';
    const TYPE_TEXT = 'text';
    const TYPE_SMALLINT = 'smallint';
    const TYPE_INTEGER = 'integer';
    const TYPE_BIGINT = 'bigint';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';
    const TYPE_DECIMAL = 'decimal';
    const TYPE_DATETIME = 'datetime';
    const TYPE_TIMESTAMP = 'timestamp';
    const TYPE_TIME = 'time';
    const TYPE_DATE = 'date';
    const TYPE_BINARY = 'binary';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_MONEY = 'money';

    public $typeMap = [
        'tinyint' => self::TYPE_SMALLINT,
        'bit' => self::TYPE_INTEGER,
        'smallint' => self::TYPE_SMALLINT,
        'mediumint' => self::TYPE_INTEGER,
        'int' => self::TYPE_INTEGER,
        'integer' => self::TYPE_INTEGER,
        'bigint' => self::TYPE_BIGINT,
        'float' => self::TYPE_FLOAT,
        'double' => self::TYPE_DOUBLE,
        'real' => self::TYPE_FLOAT,
        'decimal' => self::TYPE_DECIMAL,
        'numeric' => self::TYPE_DECIMAL,
        'tinytext' => self::TYPE_TEXT,
        'mediumtext' => self::TYPE_TEXT,
        'longtext' => self::TYPE_TEXT,
        'longblob' => self::TYPE_BINARY,
        'blob' => self::TYPE_BINARY,
        'text' => self::TYPE_TEXT,
        'varchar' => self::TYPE_STRING,
        'string' => self::TYPE_STRING,
        'char' => self::TYPE_CHAR,
        'datetime' => self::TYPE_DATETIME,
        'year' => self::TYPE_DATE,
        'date' => self::TYPE_DATE,
        'time' => self::TYPE_TIME,
        'timestamp' => self::TYPE_TIMESTAMP,
        'enum' => self::TYPE_STRING,
    ];

    /**
     * 创建索引
     * @param string $name
     * @param string $table
     * @param string|array $columns
     * @return mixed
     */
    public function index($name, $table, $columns) {
        $column = [];
        foreach ((array)$columns as $key => $item) {
            if (!is_integer($key)) {
                $column[] = $key. ' '.strtoupper($item);
                continue;
            }
            if (!is_array($item)) {
                $column[] = $item;
                continue;
            }
            $column[] = $item[0]. ' '.strtoupper($item[1]);
        }
        return $this->command()->execute('CREATE INDEX '.$name.' ON '.
            $this->addPrefix($table).
            ' ('. implode(', ', $column).')');
    }

    /**
     * 修改表结构
     * @param string $table
     * @param string $type ADD or ALTER or DROP or DROP INDEX
     * @return mixed
     */
    public function alter($table, $type = 'ADD') {
        $type = strtoupper($type);
        if ($type == 'ALTER' || $type = 'DROP') {
            $type .= ' COLUMN';
        }
        return $this->command()->execute('ALTER TABLE '.
            $this->addPrefix($table).
            ' '.$type.
            $this->getColumns());
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

    /**
     * 删除表
     * @param string|array $name
     * @return mixed
     */
    public function dropTable($name) {
        $tables = [];
        foreach ((array)$name as $item) {
            $tables[] = $this->addPrefix($item);
        }
        return $this->command()->execute('DROP TABLE '.implode(',', $tables));
    }

    /**
     * 创建表
     * @param string $name
     * @param string $engine
     * @return mixed
     */
    public function createTable($name, $engine = 'MYISAM') {
        return $this->command()->execute('CREATE TABLE '.$this->addPrefix($name).
            ' ('. $this->getColumns(). ') ENGINE = '.$engine.' DEFAULT CHARSET=UTF8;');
    }

    protected function getColumns() {
        return '';
    }

    public function getSql() {
        return '';
    }
}