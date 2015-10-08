<?php
namespace App\Lib\Db;

use App;
use App\Lib\Helper\HSql;
    
class DPdo
{
	//pdo对象  
    protected $pdo = null;  
    //用于存放实例化的对象  
    static protected $instance = null;  
    //存放表名前缀
    protected $prefix = null;
    
    //存放当前操作的错误信息
    protected $error=null;
    
    protected $result;
    
    
       
    //公共静态方法获取实例化的对象  
    static public function getInstance() {  
        if (!(self::$instance instanceof self)) {  
            self::$instance = new self();  
        }  
        return self::$instance;  
    }  
       
    //私有克隆  
    protected function __clone() {}

    /**
     * 公有构造
     *
     * @access public
     *
     * @internal param array|string $config_path 数据库的配置信息.
     */
    public function __construct() {  
        
		$config = App::config('mysql');
        $host = $config['host'];
	    $user = $config['user'];
	    $pwd = $config['password'];
	    $database = $config['database'];
	    $coding = $config['encoding'];
        $port = $config['port'];
	    $this->prefix = $config['prefix'];
        
        if(isset($this->table))
        {
            $this->table = $this->prefix.$this->table;            
        }

        
        try {  
            //$this->pdo = new \PDO('mysql:host='.$host.';port='.$port.';dbname='.$database, $user, $pwd ,
            //                     array(\PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES {$coding}"));  
            $this->pdo = new \PDO('mysql:host='.$host.';port='.$port.';dbname='.$database, $user, $pwd );
            $this->pdo ->exec('SET NAMES {$coding}');
            $this->pdo->query ( "SET character_set_client={$coding}" );
            $this->pdo->query ( "SET character_set_connection={$coding}" );
            $this->pdo->query ( "SET character_set_results={$coding}" );
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);  
        } catch (\PDOException $ex) {  
            $this->error=$ex->getMessage();
            return false;
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
    public function add(Array $addData) {  
        $addFields = array();  
        $addValues = array();  
        foreach ($addData as $key => $value) {  
            $addFields[] = $key;  
            $addValues[] = $value;  
        }  
        $addFields = implode('`,`', $addFields);  
        $addValues = implode("','", $addValues);  
        $sql = "INSERT INTO {$this->table} (`$addFields`) VALUES ('$addValues')";  
        $this->execute($sql);
        return $this->pdo->lastInsertId();  
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
    public function update(Array $updateData , Array $param) {  
        $where = $setData = '';  
        foreach ($param as $key => $value) {  
            $where .= $value.' AND ';  
        }  
        $where = 'WHERE '.substr($where, 0, -4);  
        foreach ($updateData as $key => $value) {  
            if (is_array($value)) {  
                $setData .= "`$key` = $value[0],";  
            } else {  
                $setData .= "`$key` = '$value',";  
            }  
        }  
        $setData = substr($setData, 0, -1);  
        $sql = "UPDATE {$this->table} SET $setData $where";  
        return $this->execute($sql)->rowCount();  
    }  
       
    /**
	 * 验证一条数据
	 *
	 * @access public
	 *
	 * @param array $param 条件
	 * @return string|bool 返回id,
	 */
    public function findOne(Array $param) {  
        $where = '';  
        foreach ($param as $key => $value) {  
            $where .=$value.' AND ';  
        }  
        $where = 'WHERE '.substr($where, 0, -4);  
        $sql = "SELECT * FROM {$this->table} $where LIMIT 1";  
        $result = $this->execute($sql);
        if($result->rowCount() > 0)
        {
            return $result->fetchObject();
        }else{
            return false;
        } 
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
            foreach ($param as $key=>$value) {  
            $where .= $value.' AND ';  
            }  
            $where = 'WHERE '.substr($where, 0, -4);  
        }else{
            $where='WHERE '.$param;
        }
        $sql = "DELETE FROM {$this->table} $where LIMIT 1";  
        return $this->execute($sql)->rowCount();  
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
    public function find( Array $param = array(),Array $fileld=array()) {  
        $limit = $order =$group = $where = $like = '';  
        if (is_array($param) && !empty($param)) {  
            $limit = isset($param['limit']) ? 'LIMIT '.$param['limit'] : '';  
            $order = isset($param['order']) ? 'ORDER BY '.$param['order'] : '';  
            $group = isset($param['group']) ? 'GROUP BY '.$param['group'] : '';  
            if (isset($param['where'])) {  
                foreach ($param['where'] as $key=>$value) {  
                    if(empty($where))
                    {
                        $where='WHERE'.$value;
                    }else{
                        if(is_array($value))
                        {
                            switch($value[1])
                            {
                                case "or":
                                    $where .= 'OR'.$value;
                                case "and":
                                    $where .= 'AND'.$value;
                            }
                        }else{
                            $where .= 'AND'.$value;
                        }
                    }
                }  
            }  
            /*if (isset($param['like'])) {  
                foreach ($param['like'] as $key=>$value) {  
                    $like = "WHERE $key LIKE '%$value%'";  
                }  
            }  */
        }  
        $selectFields = empty($fileld)?"*":implode(',', $fileld);  
        $sql = "SELECT $selectFields FROM {$this->table} $where $group $order $limit";  

        $stmt = $this->execute($sql);  
        $result = array();  
        while (!!$objs = $stmt->fetchObject()) {  
            $result[] = $objs;  
        }  
        
        return $result;  
    }  
       
    /**
	 * 总记录
	 *
	 * @access public
	 *
     * @param array|null $param 条件
	 * @return int 返回总数,
	 */ 
    public function count( Array $param = array()) {  
        $where = '';  
        if (isset($param['where'])) {  
            foreach ($param['where'] as $key=>$value) {  
                $where .= $value.' AND ';  
            }  
            $where = 'WHERE '.substr($where, 0, -4);  
        }  
        $sql = "SELECT COUNT(*) as count FROM {$this->table} $where";  
        $stmt = $this->execute($sql);  
        return $stmt->fetchObject()->count;  
    }  
       
    /**
	 * 得到下一个id
	 *
	 * @access public
	 *
	 * @return string 返回id,
	 */  
    public function nextId() {  
        $sql = "SHOW TABLE STATUS LIKE '{$this->table}'";  
        $stmt = $this->execute($sql);  
        return $stmt->fetchObject()->Auto_increment;  
    }  
   
    /**
	 * 执行SQL语句
	 *
	 * @access public
	 *
     * @param array $param 条件
     * @param bool $isList 返回类型
     * @param bool $need 是否需要表的前缀
	 * @return array 返回查询结果,
	 */ 
    public function findByHelper($param ,$isList = TRUE)
    {
        $result = array();
        if(!empty($param))
        {
            $sql = new HSql($this->prefix);
              
            $stmt = $this->execute($sql->getSQL($param));            //获取SQL语句
            while (!!$objs = $stmt->fetchObject()) 
            {  
                if($isList)
                {
                    $list = array();
                    foreach ($objs as $key => $value) 
                    {
                        $list[$key] = $value;
                    }
                    $result[] = $list;
                }else
                {
                   $result[] = $objs;   
                }
            }
        }
        return $result;
    }
    
    
    public function prepare($sql)
    {
        $this->result = $this->pdo->prepare($sql); 
    }
    
    public function bind($param)
    {
        foreach ($param as $key => $value) {
           $this->result->bindParam( $key, $value);
        }
    }
   
    /**
	 * 执行SQL语句
	 *
	 * @access public
	 *
     * @param array|null $param 条件
	 * @return array 返回查询结果,
	 */ 
    public function execute($sql = null) {  
        try {  
            if(!empty($sql))
            {
                $this->result = $this->pdo->prepare($sql);  
            }
            $this -> result ->execute();  
        } catch (\PDOException  $ex) {  
            $this->error=$ex->getMessage();
        }  
        return $this -> result;  
    } 
    
    /**
	 * 得到当前执行语句的错误信息
	 *
	 * @access public
	 *
	 * @return string 返回错误信息,
	 */ 
    public function getError()
    {
        return $this->error;
    }
}