<?php
namespace Zodream\Infrastructure\Database;
use Zodream\Infrastructure\ObjectExpand\ArrayExpand;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/19
 * Time: 10:30
 */

class Query extends BaseQuery {

    protected $select = array();

    protected $from = array();

    protected $where = array();

    protected $join = array();

    protected $group = array();

    protected $having = array();

    protected $order = array();

    protected $union = array();

    protected $limit;

    protected $offset;

    protected $operators = array(
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'in', 'not in', 'is', 'is not',
        'like', 'like binary', 'not like', 'between', 'not between', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to'
    );

    protected $sequence =  array(
        'select',
        'from',
        'join',
        'left',
        'inner',
        'right',
        'where',
        'group',
        'having',
        'order',
        'limit',
        'offset'
    );

    public function __construct($args = array()) {
        $this->load($args);
    }



    public function load(array $args) {
        foreach ($args as $key => $item) {
            $tag = strtolower(is_integer($key) ? array_shift($item) : $key);
            if (!in_array($tag, $this->sequence) || empty($item)) {
                continue;
            }
            $this->$tag($item);
        }
        return $this;
    }

    /**
     * @param string|array $field
     * @return $this
     */
    public function select($field = '*') {
        if (!is_array($field)) {
            $field = func_get_args();
        }
        foreach ($field as $key => $value) {
            if (is_int($key)) {
                $this->select[] = $value;
            } else {
                $this->select[] = $value. ' AS '.$key;
            }
        }
        return $this;
    }

    public function count($column = '*') {
        return $this->_selectFunction(__FUNCTION__, $column);
    }

    public function max($column)  {
        return $this->_selectFunction(__FUNCTION__, $column);
    }

    public function min($column)  {
        return $this->_selectFunction(__FUNCTION__, $column);
    }

    public function avg($column)  {
        return $this->_selectFunction(__FUNCTION__, $column);
    }

    public function sum($column)  {
        return $this->_selectFunction(__FUNCTION__, $column);
    }

    private function _selectFunction($name, $column) {
        $this->select[] = "{$name}({$column}) AS {$name}";
        return $this;
    }

    /**
     * @param string|array $tables
     * @return $this
     */
    public function from($tables) {
        if (!is_array($tables)) {
            $tables = func_get_args();
        }
        $this->from = array_merge($this->from, $tables);
        return $this;
    }

    public function where($condition, $params = array()) {
        $this->where = [$condition];
        return $this->addParam($params);
    }

    public function whereMany($condition, $params = array()) {
        $this->where = array_merge($this->where, $this->addCondition($condition));
        return $this->addParam($params);
    }

    protected function addCondition($condition) {
        if (!is_array($condition)) {
            return array(
                array(
                    $condition,
                    'AND'
                )
            );
        }
        $result = array();
        foreach ($condition as $key => $item) {
            if (!is_integer($key)) {
                $item = (array)$item;
                array_unshift($item, $key);
            }
            $result[] = $item;
        }
        return $result;
    }

    public function andWhere($condition, $params = array()) {
        $this->where[] = array(
            $condition,
            'AND'
        );
        return $this->addParam($params);
    }

    public function orWhere($condition, $params = array()) {
        $this->where[] = array(
            $condition,
            'OR'
        );
        return $this->addParam($params);
    }

    public function join($type, $table, $on = '', $params = array()) {
        $this->join[] = array($type, $table, $on);
        return $this->addParam($params);
    }

    public function inner($table, $on = '', $params = array()) {
        $this->addJoin($table, $on, 'INNER');
        return $this->addParam($params);
    }

    public function addJoin($args, $on = '', $tag = 'left') {
        if (is_array($on)) {
            if (count($on) == 2) {
                $on = $on[0].' = '.$on[1];
            } else {
                list($key, $value) = ArrayExpand::split($on);
                $on = $key.' = '.$value;
            }
        }
        $tag = strtoupper($tag);
        if (!is_array($args)) {
            $this->join[] = array( $tag.' JOIN', $this->addPrefix($args), $on);
            return;
        }
        if ($args[0] instanceof Query) {
            $this->join[] = array( $tag.' JOIN', '('.$args[0]->getSql().') '.$args[1], $on);
            return;
        }
        for ($i = 1, $length = count($args); $i < $length; $i += 2) {
            $this->join[] = array($tag.' JOIN ', $this->addPrefix($args[$i - 1]), $args[$i]);
        }
    }

    public function left($table, $on = '', $params = array()) {
        $this->addJoin($table, $on, 'LEFT');
        return $this->addParam($params);
    }

    public function right($table, $on = '', $params = array()) {
        $this->addJoin($table, $on, 'RIGHT');
        return $this->addParam($params);
    }

    public function group($columns) {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }
        $this->group = array_merge($this->group, $columns);
        return $this;
    }

    /**
     * 起别名
     * @param string $key
     * @return $this
     */
    public function alias($key) {
        if (count($this->from) == 1) {
            $this->from = array($key => current($this->from));
        }
        return $this;
    }

    public function having($condition, $params = array()) {
        $this->having = [$condition];
        return $this->addParam($params);
    }

    public function havingMany($condition, $params = array()) {
        $this->having = array_merge($this->having, $this->addCondition($condition));
        return $this->addParam($params);
    }

    public function andHaving($condition, $params = array()) {
        $this->having[] = array(
            $condition,
            'AND'
        );
        return $this->addParam($params);
    }

    public function orHaving($condition, $params = array()) {
        $this->having[] = array(
            $condition,
            'OR'
        );
        return $this->addParam($params);
    }

    public function order($args) {
        if (!is_array($args)) {
            $args = func_get_args();
        }
        // 把关联数组变成 1，asc
        foreach ($args as $key => $item) {
            if (!is_integer($key)) {
                if (is_array($item)) {
                    //'asc' => ['a', 'b']
                    foreach ($item as $value) {
                        $this->order[] = $value;
                        $this->order[] = $key;
                    }
                    continue;
                }
                // 'a' => 'b'
                $this->order[] = $key;
                $this->order[] = $item;
                continue;
            }
            if (is_array($item)) {
                // ['a', 'asc']
                $this->order[] = $item[0];
                $this->order[] = $item[1];
                continue;
            }
            $this->order[] = $item;
        }
        return $this;
    }

    public function union($sql, $all = false) {
        $this->union[] = ['query' => $sql, 'all' => $all];
        return $this;
    }

    public function limit($limit, $length = null) {
        if (!empty($length)) {
            $this->offset($limit);
            $limit = $length;
        }
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    /**
     * add build value
     * @param string|array $key
     * @param string $value
     * @return $this
     */
    public function addParam($key, $value = null) {
        $this->set($key, $value);
        return $this;
    }

    /**
     * @return string
     */
    public function getSql() {
        return $this->getSelect().
        $this->getFrom().
        $this->getJoin().
        $this->getWhere().
        $this->getGroup().
        $this->getHaving().
        $this->getOrder().
        $this->getLimit().
        $this->getOffset();
    }

    /**
     * @param bool $isArray
     * @return array|object
     */
    public function all($isArray = true) {
        if ($isArray) {
            return $this->command()->getArray($this->getSql(), $this->get());
        }
        return $this->command()->getObject($this->getSql(), $this->get());
    }

    /**
     * @return array|null
     */
    public function one() {
        $this->limit(1);
        $result = $this->all();
        if (empty($result)) {
            return null;
        }
        return current($result);
    }

    /**
     *
     * @return bool|string|int
     */
    public function scalar() {
        $result = $this->one();
        if (empty($result)) {
            return false;
        }
        return current($result);
    }

    protected function getSelect() {
        if (empty($this->select)) {
            return null;
        }
        return 'SELECT '.$this->getField();
    }

    protected function getFrom() {
        if (empty($this->from)) {
            return null;
        }
        $result = array();
        foreach ($this->from as $key => $item) {
            if (is_integer($key)) {
                $result[] = $this->addPrefix($item);
                continue;
            }
            if ($item instanceof Query) {
                $result[] = '('.$item->getSql().') ' .$key;
                continue;
            }
            $result[] = $this->addPrefix($item).' ' .$key;
        }
        return ' FROM '.implode($result, ',');
    }

    /**
     * @return string
     */
    protected function getUnion() {
        if (empty($this->union)) {
            return null;
        }
        $sql = ' ';
        foreach ($this->union as $item) {
            $sql .= 'UNION ';
            if ($item['all']) {
                $sql .= 'ALL ';
            }
            if ($item['query'] instanceof Query) {
                $sql .= $item['query']->getSql();
                continue;
            }
            if (is_array($item['query'])) {
                $sql .= (new Query())->load($item['query'])->getSql();
            }
            $sql .= $item['query'];
        }
        return $sql;
    }

    protected function getHaving() {
        if (empty($this->having)) {
            return null;
        }
        return ' Having'.$this->getCondition($this->having);
    }

    protected function getWhere() {
        if (empty($this->where)) {
            return null;
        }
        return ' WHERE'.$this->getCondition($this->where);
    }



    /**
     * 合并where 或 having 的条件
     * @param array|string $param
     * @return string
     */
    protected function getCondition($param) {
        if (is_string($param)) {
            return $param;
        }
        $sql = '';
        foreach ($param as $key => $value) {
            $val = $value;
            if (!is_numeric($key)) {
                $val = (array)$val;
                array_unshift($val, $key);
            }
            $sql .= $this->getConditionOne($val);
        }
        if (empty($sql)) {
            return null;
        }
        if (strpos($sql, 'OR') === 1) {
            return substr($sql, 3);
        }
        if (strpos($sql, 'AND') === 1) {
            return substr($sql, 4);
        }
        return $sql;
    }

    /**
     * 合成一条条件语句
     * @param string|array $arg
     * @return null|string
     */
    protected function getConditionOne($arg) {
        if (is_string($arg)) {
            return $this->getConditionLink($arg);
        }
        if (!is_array($arg)) {
            return null;
        }
        if (ArrayExpand::isAssoc($arg)) {
            return $this->getCondition($arg);
        }
        // [[], 'or']
        if (is_array($arg[0])) {
            $arg[0] = '('.$this->getCondition($arg[0]).')';
        }
        $length = count($arg);
        if ($length == 1) {
            // 'a = b'
            return $this->getConditionLink($arg[0]);
        }
        if ($length == 2) {
            if ($this->isOrOrAnd($arg[1])) {
                // ['a = b', 'or']
                return $this->getConditionLink($arg[0], $arg[1]);
            }
            // ['id', []]
            if (is_array($arg[1])) {
                $sql = [];
                foreach ($arg[1] as $item) {
                    $sql[] = "{$arg[0]} = ". $this->getValueByOperator($item);
                }
                return ' AND ('.implode(' AND ', $sql).')';
            }
            // ['a', 'b']
            return $this->getConditionLink(
                "{$arg[0]} = ". $this->getValueByOperator($arg[1]));
        }
        if ($length == 3) {
            // ['id', [], 'or']
            if (is_array($arg[1])) {
                // ['id', ['1', 'int'], '@'] 需要安全检查的
                if ($arg[2] == '@') {
                    // ['a', 'b']
                    return $this->getConditionLink(
                        "{$arg[0]} = ". $this->getValueByOperator($arg[1]));
                }
                $sql = [];
                foreach ($arg[1] as $item) {
                    $sql[] = "{$arg[0]} = ". $this->getValueByOperator($item);
                }
                return ' '.strtoupper($arg[2]) .' ('.implode(' AND ', $sql).')';
            }

            if (in_array($arg[1], $this->operators)) {
                // ['a', '=', 'b']
                return $this->getConditionLink(
                    "{$arg[0]} {$arg[1]} ". $this->getValueByOperator($arg[2], $arg[1]));
            }
            // ['a', 'b', 'or']
            return $this->getConditionLink(
                "{$arg[0]} = ". $this->getValueByOperator($arg[1]), $arg[2]);
        }
        if ($length == 4) {
            // ['id', [], 'or']
            if (is_array($arg[1])) {
                // ['id', ['1', 'int'], '@', 'or'] 需要安全检查的
                if ($arg[2] == '@') {
                    // ['a', 'b']
                    return $this->getConditionLink(
                        "{$arg[0]} = ". $this->getValueByOperator($arg[1]), $arg[3]);
                }
                // ['id', [1, 3], 'or', 'or']
                $sql = [];
                foreach ($arg[1] as $item) {
                    $sql[] = "{$arg[0]} = ". $this->getValueByOperator($item);
                }
                return ' '.strtoupper($arg[3]) .' ('.
                implode(' '. strtoupper($arg[2]).' ', $sql).')';
            }

            if ($this->isOrOrAnd($arg[3])) {
                // ['a', '=', 'b', 'or']
                return $this->getConditionLink(
                    $arg[0].' '.$arg[1]. ' '. $this->getValueByOperator($arg[2], $arg[1]),
                    $arg[3]);
            }
            // ['a', 'between', 'b', 'c']
            return $this->getConditionLink(
                $arg[0].' '.$arg[1]. ' '. $this->getValueByOperator($arg[2]). ' AND '.$this->getValueByOperator($arg[3]));
        }

        if ($length == 5) {
            if ($this->isOrOrAnd($arg[4])) {
                //['a', 'between', 'b', 'c', 'or']
                return $this->getConditionLink(
                    $arg[0].' '.$arg[1]. ' '. $this->getValueByOperator($arg[2]). ' AND '.$this->getValueByOperator($arg[3]),
                    $arg[4]);
            }
            //['a', 'between', 'b', 'and', 'c']
            return $this->getConditionLink(
                $arg[0].' '.$arg[1]. ' '. $this->getValueByOperator($arg[2]). ' '.$arg[3].' '.$this->getValueByOperator($arg[4]));
        }
        //['a', 'between', 'b', 'and', 'c', 'or']
        return $this->getConditionLink(
            $arg[0].' '.$arg[1]. ' '. $this->getValueByOperator($arg[2]). ' '.$arg[3].' '.$this->getValueByOperator($arg[4]),
            $arg[5]);
    }

    protected function getValueByOperator($value, $operator = null) {
        if ($value instanceof Query) {
            return '('.$value->getSql().')';
        }
        if (('is' == $operator || 'is not' == $operator) && is_null($value)) {
            return 'null';
        }
        if (('in' == $operator || 'not in' == $operator)) {
            if (is_array($value)) {
                $value = "'".implode("', '", $value)."'";
            }
            return '('.$value. ')';
        }
        // [a, int]
        if (is_array($value)) {
            if (count($value) == 1) {
                $value[] = 'string';
            }
            switch ($value[1]) {
                case 'int':
                case 'integer':
                case 'numeric':
                    return intval($value[0]);
                case 'bool':
                case 'boolean':
                    return boolval($value[0]);
                case 'string':
                default:
                    return "'". addslashes($value[0]). "'";
            }
        }
        // 连接查询 排除邮箱 排除网址
        if (strpos($value, '.') !== false
            && substr_count($value, '.') === 1
            && strpos($value, '@') === false) {
            return $value;
        }
        // 表内字段关联
        if (strpos($value, '@') === 0) {
            return substr($value, 1);
        }
        if (is_numeric($value)) {
            return $value;
        }
        return "'{$value}'";
    }

    /**
     * 判断是否是or 或 and 连接符
     * @param string $arg
     * @return bool
     */
    protected function isOrOrAnd($arg) {
        return is_string($arg) && in_array(strtolower($arg), array('and', 'or'));
    }

    /**
     * 把连接符换成标准格式
     * @param string $arg
     * @param string $tag
     * @return null|string
     */
    protected function getConditionLink($arg, $tag = 'and') {
        if (empty($arg)) {
            return null;
        }
        if (is_array($arg)) {
            $arg = '('. $this->getCondition($arg).')';
        }
        if (strtolower($tag) === 'or') {
            return ' OR '.$arg;
        }
        return ' AND '.$arg;
    }

    /**
     * 支持多个相同的left [$table, $where, ...]
     * @return string
     */
    protected function getJoin() {
        if (empty($this->join)) {
            return null;
        }
        $sql = '';
        foreach ($this->join as $item) {
            $sql .= " {$item[0]} {$item[1]}";
            if (!empty($item[2])) {
                $sql .= " ON {$item[2] }";
            }
        }
        return $sql;
    }

    /**
     *
     * 关键字 DISTINCT 唯一 AVG() COUNT() FIRST() LAST() MAX()  MIN() SUM() UCASE() 大写  LCASE()
     * MID(column_name,start[,length]) 提取字符串 LEN() ROUND() 舍入 NOW() FORMAT() 格式化
     * @return string
     */
    protected function getField() {
        if (empty($this->select)) {
            return '*';
        }
        $result = array();
        foreach ((array)$this->select as $key => $item) {
            if (is_integer($key)) {
                $result[] = $item;
            } else {
                $result[] = "{$item} AS {$key}";
            }
        }
        return implode($result, ',');
    }

    protected function getGroup() {
        if (empty($this->group)) {
            return null;
        }
        return ' GROUP BY '.implode(',', (array)$this->group);
    }

    protected function getOrder() {
        if (empty($this->order)) {
            return null;
        }
        $result = array();
        for ($i = 0, $length = count($this->order); $i < $length; $i ++) {
            $sql = $this->order[$i];
            if ($i < $length - 1 && in_array(strtolower($this->order[$i + 1]), array('asc', 'desc')) ) {
                $sql .= ' '.strtoupper($this->order[$i + 1]);
                $i ++;
            }
            $result[] = $sql;
        }
        return ' ORDER BY '.implode($result, ',');
    }

    protected function getLimit() {
        if (empty($this->limit)) {
            return null;
        }
        $param = (array)$this->limit;
        if (count($param) == 1) {
            return " LIMIT {$param[0]}";
        }
        $param[0] = intval($param[0]);
        $param[1] = intval($param[1]);
        if ($param[0] < 0) {
            $param[0] = 0;
        }
        return " LIMIT {$param[0]},{$param[1]}";
    }

    protected function getOffset() {
        if (empty($this->offset)) {
            return null;
        }
        return ' OFFSET '.intval($this->offset);
    }


}