<?php 
namespace App\Body\Db;
/*
* mysql 
* 
* @author Jason
* @time 2015-11.29
*/

class Mysql {
	/**
	 * 连接标识符
	 *
	 * @var mysql
	 */
	protected $mysql;
	
	//用于存放实例化的对象
	protected static $instance = null;
	
	//存放当前操作的错误信息
	protected $error           = null;
	
	protected $result;
	
	/**
	 * 公共静态方法获取实例化的对象
	 */
	public static function getInstance(array $config) {
		if (is_null(static::$instance)) {
			static::$instance = new static($config);
		}
		return static::$instance;
	}
	
	//私有克隆
	protected function __clone() {}
	
	
	/**
	 * 数据库的配置信息
	 *
	 * @var string
	 */
	protected $host;
	protected $username;
	protected $password;
	protected $db;
	protected $port;
	protected $charset;
	
	/**
	 * 公有构造
	 *
	 * @access public
	 *
	 * @internal param array|string $config_path 数据库的配置信息.
	 */
	private function __construct($config) {
		$this->host     = $config['host'];
		$this->username = $config['user'];
		$this->password = $config['password'];
		$this->db       = $config['database'];
		$this->charset  = $config['encoding'];
		$this->port     = $config['port'];
		$this->connect();
	}
	
	/**
	 * 连接数据库
	 *
	 */
	private function connect() {
		if (empty($this->host)) {
			die ('Mysql host is not set');
		}
		$this->mysql = mysql_connect($this->host.':'.$this->port, $this->username, $this->password)
		or die('There was a problem connecting to the database');
		mysql_select_db($this->db, $this->mysql) or die ("Can\'t use {$this->db} : " . mysql_error());
		if ($this->charset) {
			mysql_query('SET NAMES '.$this->charset, $this->mysql);
		}
	}
	
	/**
	 * 返回连接符，能使用原生语法
	 */
	public function mysql () {
		if (!$this->mysql) {
			$this->connect();
		}
		return $this->mysql;
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
		$this->execute($sql);
		return  $this->rows();
	}
	
	/**
	 * 删除
	 * @param string $sql
	 * @return integer 删除的行数
	 */
	public function delete($sql) {
		$this->execute($sql);
		return $this->rows();
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
		while (!!$objs = mysql_fetch_object($this->result) ) {
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
		$result = array();
		while (!!$objs = mysql_fetch_assoc($this->result) ) {
			$result[] = $objs;
		}
		return $result;
	}
	
	/**
	 * 返回上一步执行受影响的行数
	 *
	 * @access public
	 *
	 */
	public function rows($end = TRUE) {
		$rows = mysql_affected_rows($this->mysql);
		if ($end) {
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
	public function lastInsertId($end = TRUE) {
		$id = mysql_insert_id($this->mysql);
		if($end) {
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
	public function rowCount($end = TRUE) {
		$count = mysql_num_rows($this->result);
		if($end) {
			$this->close();
		}
		return $count;
	}
	
	/**
	 * 执行SQL语句
	 *
	 * @access public
	 *
	 * @param string $sql 多行查询语句
	 */
	public function execute($sql)
	{
		if (empty($sql)) {
			return;
		}
		$this->result = mysql_query($sql, $this->mysql);
		return $this->result;
	}
	
	/**
	 * 关闭和清理
	 *
	 * @access public
	 *
	 *
	 */
	public function close() {
		if (!empty($this->result) && !is_bool($this->result)) {
			mysql_free_result($this->result);
		}
		mysql_close($this->mysql);
	}
	
	public function getError() {
		return mysql_error($this->mysql);
	}
	
	public function __destruct() {
		$this->close();
	}
}