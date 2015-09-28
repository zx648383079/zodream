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
	 * @param array $_addData 需要添加的集合
	 * @return int 返回影响的行数,
	 */
    public function add(Array $_addData) {  
        $_addFields = array();  
        $_addValues = array();  
        foreach ($_addData as $_key=>$_value) {  
            $_addFields[] = $_key;  
            $_addValues[] = $_value;  
        }  
        $_addFields = implode(',', $_addFields);  
        $_addValues = implode("','", $_addValues);  
        $_sql = "INSERT INTO {$this->table} ($_addFields) VALUES ('$_addValues')";  
        $this->execute($_sql);
        return $this->pdo->lastInsertId();  
    }  
       
    /**
	 * 修改记录
	 *
	 * @access public
	 *
	 * @param array $_param 条件
     * @param array $_updateData 需要修改的内容
	 * @return int 返回影响的行数,
	 */
    public function update(Array $_updateData , Array $_param) {  
        $_where = $_setData = '';  
        foreach ($_param as $_key=>$_value) {  
            $_where .= $_value.' AND ';  
        }  
        $_where = 'WHERE '.substr($_where, 0, -4);  
        foreach ($_updateData as $_key=>$_value) {  
            if (is_array($_value)) {  
                $_setData .= "$_key = $_value[0],";  
            } else {  
                $_setData .= "$_key = '$_value',";  
            }  
        }  
        $_setData = substr($_setData, 0, -1);  
        $_sql = "UPDATE {$this->table} SET $_setData $_where";  
        return $this->execute($_sql)->rowCount();  
    }  
       
    /**
	 * 验证一条数据
	 *
	 * @access public
	 *
	 * @param array $_param 条件
	 * @return string|bool 返回id,
	 */
    public function findOne(Array $_param) {  
        $_where = '';  
        foreach ($_param as $_key=>$_value) {  
            $_where .=$_value.' AND ';  
        }  
        $_where = 'WHERE '.substr($_where, 0, -4);  
        $_sql = "SELECT * FROM {$this->table} $_where LIMIT 1";  
        $result = $this->execute($_sql);
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
	 * @param array|string $_param 条件
	 * @return int 返回影响的行数,
	 */
    public function delete($_param) {  
        $_where = '';  
        if(is_array($_param))
        {
            foreach ($_param as $_key=>$_value) {  
            $_where .= $_value.' AND ';  
            }  
            $_where = 'WHERE '.substr($_where, 0, -4);  
        }else{
            $_where='WHERE '.$_param;
        }
        $_sql = "DELETE FROM {$this->table} $_where LIMIT 1";  
        return $this->execute($_sql)->rowCount();  
    }  
       
    /**
	 * 查询数据
	 *
	 * @access public
	 *
     * @param array $_fileld 要显示的字段
     * @param array|null $_param 条件
	 * @return array 返回查询结果,
	 */  
    public function find( Array $_param = array(),Array $_fileld=array()) {  
        $_limit = $_order =$_group = $_where = $_like = '';  
        if (is_array($_param) && !empty($_param)) {  
            $_limit = isset($_param['limit']) ? 'LIMIT '.$_param['limit'] : '';  
            $_order = isset($_param['order']) ? 'ORDER BY '.$_param['order'] : '';  
            $_group = isset($_param['group']) ? 'GROUP BY '.$_param['group'] : '';  
            if (isset($_param['where'])) {  
                foreach ($_param['where'] as $_key=>$_value) {  
                    if(empty($_where))
                    {
                        $_where='WHERE'.$_value;
                    }else{
                        if(is_array($_value))
                        {
                            switch($_value[1])
                            {
                                case "or":
                                    $_where .= 'OR'.$_value;
                                case "and":
                                    $_where .= 'AND'.$_value;
                            }
                        }else{
                            $_where .= 'AND'.$_value;
                        }
                    }
                }  
            }  
            /*if (isset($_param['like'])) {  
                foreach ($_param['like'] as $_key=>$_value) {  
                    $_like = "WHERE $_key LIKE '%$_value%'";  
                }  
            }  */
        }  
        $_selectFields = empty($_fileld)?"*":implode(',', $_fileld);  
        $_sql = "SELECT $_selectFields FROM {$this->table} $_where $_group $_order $_limit";  

        $_stmt = $this->execute($_sql);  
        $_result = array();  
        while (!!$_objs = $_stmt->fetchObject()) {  
            $_result[] = $_objs;  
        }  
        
        return $_result;  
    }  
       
    /**
	 * 总记录
	 *
	 * @access public
	 *
     * @param array|null $_param 条件
	 * @return int 返回总数,
	 */ 
    public function count( Array $_param = array()) {  
        $_where = '';  
        if (isset($_param['where'])) {  
            foreach ($_param['where'] as $_key=>$_value) {  
                $_where .= $_value.' AND ';  
            }  
            $_where = 'WHERE '.substr($_where, 0, -4);  
        }  
        $_sql = "SELECT COUNT(*) as count FROM {$this->table} $_where";  
        $_stmt = $this->execute($_sql);  
        return $_stmt->fetchObject()->count;  
    }  
       
    /**
	 * 得到下一个id
	 *
	 * @access public
	 *
	 * @return string 返回id,
	 */  
    public function nextId() {  
        $_sql = "SHOW TABLE STATUS LIKE '{$this->table}'";  
        $_stmt = $this->execute($_sql);  
        return $_stmt->fetchObject()->Auto_increment;  
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
              
            $_stmt = $this->execute($sql->getSQL($param));            //获取SQL语句
            while (!!$_objs = $_stmt->fetchObject()) 
            {  
                if($isList)
                {
                    $list = array();
                    foreach ($_objs as $_key => $_value) 
                    {
                        $list[$_key] = $_value;
                    }
                    $result[] = $list;
                }else
                {
                   $result[] = $_objs;   
                }
            }
        }
        return $result;
    }
    
    
    public function prepare($_sql)
    {
        $this->result = $this->pdo->prepare($_sql); 
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
     * @param array|null $_param 条件
	 * @return array 返回查询结果,
	 */ 
    public function execute($_sql = null) {  
        try {  
            if(!empty($_sql))
            {
                $this->result = $this->pdo->prepare($_sql);  
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