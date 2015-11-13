<?php
namespace App\Lib\Db;

use App;
use App\Lib\Helper\HSql;
    
class DPdo implements IBase {
	//pdo对象  
    protected $pdo             = null;  
    //用于存放实例化的对象  
    protected static $instance = null;
    
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
    private function __construct() {  
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
     * 查询
     * @param string $sql
     * @return array
     */
    public function select($sql) {
    	return $this->getArray($sql);
    }
    
    /**
     * 插入
     * @param string $sql
     * @return integer id
    */
    public function insert($sql) {
    	$this->execute($sql);
    	return $this->lastInsertId();
    }
    
    /**
     * 修改
     * @param string $sql
     * @return integer 改变的行数
    */
    public function update($sql){
    	return $this->execute($sql)->rowCount();
    }
    
    /**
     * 删除
     * @param string $sql
     * @return integer 删除的行数
    */
    public function delete($sql) {
    	return $this->execute($sql)->rowCount();
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
    	if (empty($sql)) {
    		return;
    	}
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
    
    /**
     * 获取Object结果集
     * @param string $sql
     * @return multitype:mixed
     */
    public function getObject($sql = null) {
    	$this->execute($sql);
    	$result = array();
    	while (!!$objs = $this->result->fetchObject()) {
    		$result[] = $objs;
    	}
    	return $result;
    }
    
    /**
     * 获取关联数组
     * @param string $sql
     */
    public function getArray($sql = null) {
    	$this->execute($sql);
    	return $this->result->fetchAll(\PDO::FETCH_ASSOC);
    }
}