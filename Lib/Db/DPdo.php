<?php
namespace App\Lib\Db;

use App;
use App\Lib\Helper\HSql;
    
class DPdo implements IBase {
	//pdo对象  
    protected $pdo             = null;  
    //用于存放实例化的对象  
    static protected $instance = null;  
    //存放表名前缀
    public $prefix             = null;
    
    //存放当前操作的错误信息
    protected $error           = null;
    
    protected $result;
    
    
       
    /**
     * 公共静态方法获取实例化的对象 
     */ 
    public static function getInstance() {  
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
		$config       = App::config('mysql');
        $host         = $config['host'];
	    $user         = $config['user'];
	    $pwd          = $config['password'];
	    $database     = $config['database'];
	    $coding       = $config['encoding'];
        $port         = $config['port'];
	    $this->prefix = $config['prefix'];
        
        try {  
            //$this->pdo = new \PDO('mysql:host='.$host.';port='.$port.';dbname='.$database, $user, $pwd ,
            //                     array(\PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES {$coding}"));  
            $this->pdo = new \PDO ('mysql:host='.$host.';port='.$port.';dbname='.$database, $user, $pwd);
            $this->pdo->exec ('SET NAMES {$coding}');
            $this->pdo->query ( "SET character_set_client={$coding}" );
            $this->pdo->query ( "SET character_set_connection={$coding}" );
            $this->pdo->query ( "SET character_set_results={$coding}" );
            $this->pdo->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);  
        } catch (\PDOException $ex) {  
            $this->error = $ex->getMessage();
            return false;
        }  
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
            $sql  = new HSql($this->prefix);
            $stmt = $this->execute($sql->getSQL($param));            //获取SQL语句
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
     * 获取最后修改的id
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * 预处理
     * @param unknown $sql
     */
    public function prepare($sql) {
        $this->result = $this->pdo->prepare($sql); 
    }
    
    /**
     * 绑定值
     * @param unknown $param
     */
    public function bind($param) {
        foreach ($param as $key => $value) {
           $this->result->bindParam($key, $value);
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
            if (!empty($sql)) {
                $this->result = $this->pdo->prepare($sql);  
            }
            $this -> result ->execute();  
        } catch (\PDOException  $ex) {  
            $this->error = $ex->getMessage();
        }  
        return $this->result;  
    } 
    
    /**
	 * 得到当前执行语句的错误信息
	 *
	 * @access public
	 *
	 * @return string 返回错误信息,
	 */ 
    public function getError() {
        return $this->error;
    }
    
    public function getObject() {
    	
    }
    
    public function getArray() {
    	return $this->result->fetchAll(\PDO::FETCH_ASSOC);
    }
}