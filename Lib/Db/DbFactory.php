<?php
namespace App\Lib\Db;

use App\Lib\Helper\HSql;

class DbFactory {
    protected $db = null;
    
    public function __construct() {
        $this->db = DPdo::getInstance();
        if (isset($this->table)) {
            $this->table = $this->db->prefix. $this->table;            
        }
    }
    
	 /**
	 * 新增记录
	 *
	 * @access public
	 *
	 * @param array $addData 需要添加的集合
	 * @return int 返回影响的行数,
	 */
    public function add($addData) {
        $addFields = array();  
        $addValues = array();  
        foreach ($addData as $key => $value) {  
            $addFields[] = $key;  
            $addValues[] = $value;  
        }  
        $addFields = implode('`,`', $addFields);  
        $addValues = implode("','", $addValues);  
        $sql       = "INSERT INTO {$this->table} (`$addFields`) VALUES ('$addValues')";  
        return $this->db->insert($sql);  
    }  
       
    /**
	 * 修改记录
	 *
	 * @access public
	 *
	 * @param array $param 条件
     * @param array $updateData 需要修改的内容
	 * @return int 返回影响的行数,
	 */
    public function update($updateData , $param) {
        $where = $setData = '';
        if (is_array($param)) {
            foreach ($param as $key => $value) {  
                $where .= $value.' AND ';  
            }  
            $where = 'WHERE '.substr($where, 0, -4);  
        } else {
            $where = 'WHERE '.$param;
        }
        foreach ($updateData as $key => $value) {  
            if (is_array($value)) {  
                $setData .= "`$key` = $value[0],";  
            } else {  
                $setData .= "`$key` = '$value',";  
            }  
        }  
        $setData = substr($setData, 0, -1);  
        $sql     = "UPDATE {$this->table} SET $setData $where";  
        return $this->db->update($sql);  
    }  
     
    /**
	* 设置bool值
	*
	* @param string $filed
	* @param string $where
	* @return int
	*/
	public function updateBool($filed, $where) {
		$sql =  "UPDATE {$this->table} SET {$filed} = CASE WHEN {$filed} = 1 THEN 0 ELSE 1 END WHERE ";
		$sql .= $where;
		return $this->db->update($sql);
	}
    
    /**
	* int加减
	*
	* @param string $filed
	* @param string $where
	* @param string $num
	* @return int
	*/
	public function updateOne($filed, $where, $num = 1) {
		if ($num >= 0) {
            $num = '+'.$num;
        }
		$sql = "UPDATE {$this->table} SET {$filed} = {$filed} {$num} WHERE $where";
		return $this->db->update($sql);
	}
      
    /**
	 * 查询一条数据
	 *
	 * @access public
	 *
	 * @param array|string $param 条件
	 * @return array,
	 */
    public function findOne($param , $filed = '*') {
        $where = '';  
        if ( is_array($param) ) {
            foreach ($param as $key => $value) {  
                $where .=$value.' AND ';  
            }  
            $where = 'WHERE '.substr($where, 0, -4);  
        } else if (is_string($param)) {
            $where = 'WHERE '.$param;
        }
        $sql    = "SELECT {$filed} FROM {$this->table} {$where} LIMIT 1";  
        $result = $this->db->select($sql);
        return array_shift($result);
    }  
    
    /**
     * 根据id 查找值
     * @param unknown $id
     * @param string $filed
     * @return array 
     */
	public function findById($id, $filed = '*') {
		$sql = "SELECT {$filed} FROM {$this->table} WHERE id = {$id} LIMIT 1";
		$result = $this->db->select($sql);
		return array_shift($result);
	}
       
    /**
	 * 删除第一条数据
	 *
	 * @access public
	 *
	 * @param array|string $param 条件
	 * @return int 返回影响的行数,
	 */
    public function delete($param) {  
        $where = '';  
        if(is_array($param))
        {
            foreach ($param as $key => $value) {  
            $where .= $value.' AND ';  
            }  
            $where = 'WHERE '.substr($where, 0, -4);  
        } else {
            $where = 'WHERE '.$param;
        }
        $sql = "DELETE FROM {$this->table} $where";
        return $this->db->delete($sql);  
    }  
    
    public function deleteById($id) {
    	return $this->delete('id = '.$id);
    }
       
    /**
	 * 查询数据
	 *
	 * @access public
	 *
     * @param array $fileld 要显示的字段
     * @param array|null $param 条件
	 * @return array 返回查询结果,
	 */  
    public function find($param = array(),$fileld = array()) {
        $limit = $order = $group = $where = $like = '';  
        if (is_array($param) && !empty($param)) {  
            $limit = isset($param['limit']) ? 'LIMIT '.$param['limit'] : '';  
            $order = isset($param['order']) ? 'ORDER BY '.$param['order'] : '';  
            $group = isset($param['group']) ? 'GROUP BY '.$param['group'] : '';  
            if (isset($param['where'])) {  
                foreach ($param['where'] as $key => $value) {  
                    if (empty($where)) {
                        $where = 'WHERE '.$value;
                    } else {
                        if (is_array($value)) {
                            switch ($value[1]) {
                                case "or":
                                    $where .= ' OR '.$value;
                                case "and":
                                    $where .= ' AND '.$value;
                            }
                        } else {
                            $where .= ' AND '.$value;
                        }
                    }
                }  
            }
        }  
        $selectFields = empty($fileld) ? '*' : implode(',', $fileld);  
        $sql    = "SELECT $selectFields FROM {$this->table} $where $group $order $limit";  
        $this->db->execute($sql);
        return $this->db->select($sql);  
    }  
       
    /**
	 * 总记录
	 *
	 * @access public
	 *
     * @param array|null $param 条件
	 * @return int 返回总数,
	 */ 
    public function count($param = array()) {
        $where = '';  
         if (is_array($param)) {  
            foreach ($param['where'] as $key => $value) {  
                $where .= $value.' AND ';  
            }  
            $where = 'WHERE '.substr($where, 0, -4);  
        } else {
            $where = 'WHERE '.$param;
        } 
        $sql  = "SELECT COUNT(*) as count FROM {$this->table} $where";  
        $result = $this->db->select($sql);
        return $result[0]['count'];  
    }  
       
    /**
	 * 得到下一个id
	 *
	 * @access public
	 *
	 * @return string 返回id,
	 */  
    public function nextId() {  
        $sql  = "SHOW TABLE STATUS LIKE '{$this->table}'";  
        $stmt = $this->db->execute($sql);  
        return $stmt->fetchObject()->Auto_increment;  
    } 
    
    /**
	 * 执行SQL语句
	 *
	 * @access public
	 *
     * @param array $param 条件
     * @param bool $isList 返回类型
	 * @return array 返回查询结果,
	 */ 
    public function findByHelper($param, $isList = TRUE) {
        $result = array();
        if (!empty($param)) {
            $sql  = new HSql($this->db->prefix);
            $stmt = $this->db->execute($sql->getSQL($param));            //获取SQL语句
            while (!!$objs = $stmt->fetchObject()) {  
                if ($isList) {
                    $list = array();
                    foreach ($objs as $key => $value) {
                        $list[$key] = $value;
                    }
                    $result[] = $list;
                } else {
                   $result[] = $objs;   
                }
            }
        }
        return $result;
    }
    
    /**
     * 获取错误信息
     */
    public function getError() {
        return $this->db->getError();
    }
}