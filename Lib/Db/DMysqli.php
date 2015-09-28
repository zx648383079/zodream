<?php
namespace App\Lib\Db;
/**
 *	数据库操作类 
 *
 *
 **/
 
use App;
use App\Lib\Helper\HSql;
 
class DMysqli implements IBase
{
    /**
     * 表前缀
     *
     * @var string
     */
    private $prefix = '';
    /**
     * 连接标识符
     *
     * @var mysqli
     */
    private $_mysqli;
    
    /**
     * 数据库的配置信息
     *
     * @var string
     */
    private $host;
    private $username;
    private $password;
    private $db;
    private $port;
    private $charset;

    private $result;

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $db
     * @param int $port
     */
    public function __construct()
    {
        $config = App::config('mysql');
        $this->host = $config['host'];
	    $this->username = $config['user'];
	    $this->password = $config['password'];
	    $this->db = $config['database'];
	    $this->charset = $config['encoding'];
        $this->port = $config['port'];
	    $this->prefix=$config['prefix'];
        
        if( isset( $this->table ) )
        {
            $this->table = $this->prefix.$this->table;            
        }
        
        $this->connect();
    }

    /**
     * 连接数据库
     *
     */
    private function connect()
    {

        if (empty ($this->host))
            die ('Mysql host is not set');

        $this->_mysqli = new \mysqli ($this->host, $this->username, $this->password, $this->db, $this->port)
            or die('There was a problem connecting to the database');
        /* check connection */
        /*if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }*/
        
        if ($this->charset)
            $this->_mysqli->set_charset ($this->charset);
    }

    /**
     * 返回连接符，能使用原生语法
     */
    public function mysqli ()
    {
        if (!$this->_mysqli)
            $this->connect();
        return $this->_mysqli;
    }
    
    /**
	 * 新增记录
	 *
	 * @access public
	 *
	 * @param array $_addData 需要添加的集合
	 * @return int 返回影响的行数,
	 */
    public function add(Array $_addData) 
    {  
        $_addFields = array();  
        $_addValues = array();  
        foreach ($_addData as $_key=>$_value) 
        {  
            $_addFields[] = $_key;  
            $_addValues[] = $_value;  
        }  
        $_addFields = implode(',', $_addFields);  
        $_addValues = implode("','", $_addValues);  
        $_sql = "INSERT INTO {$this->table} ($_addFields) VALUES ('$_addValues')";  
        
        return $this->execute($_sql)->lastId();  
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
    public function update(Array $_param, Array $_updateData) 
    {  
        $_where = $_setData = '';  
        foreach ($_param as $_key=>$_value) 
        {  
            $_where .= $_value.' AND ';  
        }  
        $_where = 'WHERE '.substr($_where, 0, -4);  
        foreach ($_updateData as $_key=>$_value) 
        {  
            if (is_array($_value)) 
            {  
                $_setData .= "$_key=$_value[0],";  
            } else {  
                $_setData .= "$_key='$_value',";  
            }  
        }  
        $_setData = substr($_setData, 0, -1);  
        $_sql = "UPDATE {$this->table} SET $_setData $_where";  
        return $this->execute($_sql)->rows();  
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
            foreach ($_param as $_key=>$_value) 
            {  
                $_where .= $_value.' AND ';  
            }  
            $_where = 'WHERE '.substr($_where, 0, -4);  
        }else
        {
            $_where='WHERE '.$_param;
        }
        $_sql = "DELETE FROM {$this->table} $_where LIMIT 1";  
        return $this->execute($_sql)->rows();  
    } 
    
    /**
	 * 验证一条数据
	 *
	 * @access public
	 *
	 * @param array $_param 条件
	 * @return string|bool 返回id,
	 */
    public function findOne(Array $_param) 
    {  
        $_where = '';  
        foreach ($_param as $_key=>$_value) 
        {  
            $_where .=$_value.' AND ';  
        }  
        $_where = 'WHERE '.substr($_where, 0, -4);  
        $_sql = "SELECT * FROM {$this->table} $_where LIMIT 1";  
        $result = $this->execute($_sql);
        if( $result->rowCount(FALSE) > 0)
        {
            return $result->getObject()[0];
        } else
        {
            $this->close();
            return false;
        } 
    }
    
    /**
	 * 执行简单查询
	 *
	 * @access public
	 *
     * @param array $param 条件
     * @param bool $filed 要显示的字段
	 * @return $this 返回,
	 */
    public function find( $param ='' , $filed = '*' )
    {
        $_sql = "SELECT {$filed} FROM {$this->table} ";
        if(!empty($param))
        {
            $_sql .= $param;
        }
        $this->execute($_sql);
        $_result = $this->getList();
        return $_result;
    }
    
    /**
	 * 执行SQL语句
	 *
	 * @access public
	 *
     * @param array $param 条件
     * @param bool $islist 返回类型
     * @param bool $need 是否需要表的前缀
	 * @return array 返回查询结果,
	 */ 
    public function findByHelper($param , $islist = false)
    {
        $_result = array();
        if(!empty($param))
        {
            $sql = new HSql($prefix);
              
            $this->execute($sql->getSQL($param));            //获取SQL语句
            $_result = $islist?$this->getList():$this->getObject();
        }
        return $_result;
    }
    
    /**
	* 查询计数
	*	
	* @param array|string $where 数据的条件
	*/
	public function count( $_param = '')
    {
        $_where = '';  
        if (isset($_param['where'])) {  
            foreach ($_param['where'] as $_key=>$_value) {  
                $_where .= $_value.' AND ';  
            }  
            $_where = 'WHERE '.substr($_where, 0, -4);  
        }  
        $_sql = "SELECT COUNT(*) as count FROM {$this->table} $_where";  
        $this->execute($_sql);
        return $this->getObject()->count;
    }
    
    /**
	 * 返回上一步执行受影响的行数
	 *
	 * @access public
	 *
	 */
    public function rows( $end = TRUE )
    {
        $rows = mysqli_affected_rows($this->_mysqli);
        if($end)
        {
            $this->close();                            
        }
        return $rows;
    }
    
    /**
	 * 返回上一步执行INSERT生成的id
	 *
	 * @access public
	 *
	 */
    public function lastId($end = TRUE)
    {
        $id = mysqli_insert_id($this->_mysqli);
        if($end)
        {
            $this->close();                            
        }       
        return $id;
    }
    /**
	 * 返回结果集的行数
	 *
	 * @access public
	 *
	 */
    public function rowCount($end = TRUE)
    {
        $count = mysqli_num_rows($this->result);
        if($end)
        {
            $this->close();                            
        }
        return $count;
    }
    /**
	 * 返回关联数组
	 *
	 * @access public
	 *
	 */
    public function getList($end = TRUE)
    {
        $_result = array();
        if(!is_bool($this->result))
        {
            while (!!$_objs = mysqli_fetch_assoc($this->result) ) {  
                $_result[] = $_objs;  
            }
        }else{
            $result = $this->result;
        }
        
        if($end)
        {
            $this->close();                            
        }
        return $_result;
    }
    /**
	 * 返回对象数组
	 *
	 * @access public
	 *
	 */
    public function getObject($end = TRUE)
    {
        $_result = array();
        while (!!$_objs = mysqli_fetch_object($this->result) ) {  
            $_result[] = $_objs;  
        }
        
        if($end)
        {
            $this->close();                            
        }
        return $_result;
    }
    /**
	 * 执行SQL语句
	 *
	 * @access public
	 *
     * @param string $_sql 多行查询语句
	 */ 
    public function execute($_sql)
    {
        $this->result = $this->_mysqli->query($_sql);
        return $this;
    }
    
    /**
	 * 预执行SQL语句，并绑定值  ？
	 *
	 * @access public
	 *
     * @param string $_sql SQL语句
     * @param array $param 参数
	 */ 
    public function prepare( $_sql , $param)
    {
        $this->result = mysqli_prepare($this->_mysqli,$_sql);
        mysqli_stmt_bind_param($this->result , $param );
        mysqli_stmt_execute($this->result);
    
        /* bind result variables */
        mysqli_stmt_bind_result($this->result, $district);
    
        /* fetch value */
        mysqli_stmt_fetch($this->result);
    
        printf("%s is in district %s\n", $city, $district);
    
        /* close statement */
        mysqli_stmt_close($this->result);
        $this->close();
    }
    
    /**
	 * 执行多行SQL语句
	 *
	 * @access public
	 *
     * @param string $query 多行查询语句
	 */ 
    public function multi_query($query)
    {
        $_result = array();
        if (mysqli_multi_query($this->_mysqli,$query)) 
        {                                           //执行多个查询
            do {
                    if ($this->result = mysqli_store_result($this->_mysqli)) 
                    {
                        $_result[] = $this->getList();
                        mysqli_free_result($this->result);
                    }
                        /*if (mysqli_more_results($this_mysqli)) {
                                echo ("-----------------<br>");                   //连个查询之间的分割线
                        }*/
                } while (mysqli_next_result($this->_mysqli));
        }
        
        $this->close();
        
        return $_result;
    }
    
    /**
	 * 关闭和清理
	 *
	 * @access public
	 *
     * 
	 */
    public function close()
    {
        if(!empty($this->result) && !is_bool($this->result))
        {
            mysqli_free_result($this->result);
        }
        
        mysqli_close($this->_mysqli);
    }
    
    public function getError()
    {
        return mysqli_error($this->_mysqli);
    }
}
