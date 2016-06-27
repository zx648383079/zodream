# Query.php

## where

    ['where' => $where]

$where 的类型如下

    'a = b'             AND a = b
    ['a = b']            AND a = b
    ['a' => 'b']        AND a = 'b'
    [['a', 'b']]            AND a = 'b'
    [['a', '@b']]               AND a = b
    ['a' => ['b', 'or']]        OR a = 'b'
    [['a', 'b', 'or']]              OR a = 'b'
    [[['a' => 'b', 'b' => 'c']]]        AND (a => 'b' AND b => 'c')
    [['a', 'in', [1, 2]]]               AND a in (1, 2)
    [['a', ['a', 'b']]]                 AND (a = 'a' AND a = 'b')
    [['a', ['a', 'b'], 'or']]                 OR (a = 'a' AND a = 'b')
    [['a', ['a', 'b'], 'or', 'and']]                 AND (a = 'a' OR a = 'b')
    [['a', ['a', 'int'], '@', 'or']]                 OR a = 0
    [['a', 'between', 1, 2]]               AND a between 1 AND 2
    [['a', 'between', 1, 'and' 2]]               AND a between 1 AND 2

### 更新时间：2016-06-27 19:31:00