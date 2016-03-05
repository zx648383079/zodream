<?php 
namespace Zodream\Infrastructure\Database;
/**
* mysqli 
* 
* @author Jason
*/

class Mysqli extends Database {
	
	/**
	 * 连接数据库
	 *
	 */
	protected function connect() {
		if (empty($this->configs)) {
			die ('Mysql host is not set');
		}
		$this->driver = new \mysqli(
				$this->configs['host'], 
				$this->configs['user'], 
				$this->configs['password'], 
				$this->configs['database'], 
				$this->configs['port']
		)
		or die('There was a problem connecting to the database');
		/* check connection */
		/*if (mysqli_connect_errno()) {
		 printf("Connect failed: %s\n", mysqli_connect_error());
		 exit();
		}*/
		if (isset($this->configs['encoding'])) {
			$this->driver->set_charset($this->configs['encoding']);
		}
	}
	
	/**
	 * 预处理
	 * @param unknown $sql
	 */
	public function prepare($sql) {
		$this->result = $this->driver->prepare($sql);
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
		while (!!$objs = mysqli_fetch_object($this->result) ) {
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
		while (!!$objs = mysqli_fetch_assoc($this->result) ) {
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
	public function rowCount() {
		return mysqli_affected_rows($this->driver);
	}
	
	/**
	 * 返回上一步执行INSERT生成的id
	 *
	 * @access public
	 *
	 */
	public function lastInsertId() {
		return mysqli_insert_id($this->driver);
	}
	
	/**
	 * 执行SQL语句
	 *
	 * @access public
	 *
	 * @param string $sql 多行查询语句
	 */
	public function execute($sql) {
		if (empty($sql)) {
			return;
		}
		$this->result = $this->driver->query($sql);
		return $this->result;
	}
	
	/**
	 * 预执行SQL语句，并绑定值  ？
	 *
	 * @access public
	 *
	 * @param string $sql SQL语句
	 * @param array $param 参数
	 */
	public function prepare($sql, $param) {
		$this->result = mysqli_prepare($this->driver, $sql);
		mysqli_stmt_bind_param($this->result, $param );
		mysqli_stmt_execute($this->result);
		mysqli_stmt_bind_result($this->result, $district);
		mysqli_stmt_fetch($this->result);
		printf("%s is in district %s\n", $city, $district);
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
	public function multi_query($query)  {
		$result = array();
		if (mysqli_multi_query($this->driver, $query)) {                                           //执行多个查询
			do {
				if ($this->result = mysqli_store_result($this->driver)) {
					$result[] = $this->getList();
					mysqli_free_result($this->result);
				}
				/*if (mysqli_more_results($this_mysqli)) {
				 echo ("-----------------<br>");                   //连个查询之间的分割线
				 }*/
			} while (mysqli_next_result($this->driver));
		}
		$this->close();
		return $result;
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
			mysqli_free_result($this->result);
		}
		mysqli_close($this->driver);
	}
	
	public function getError() {
		return mysqli_error($this->driver);
	}
	
	public function __destruct() {
		$this->close();
	}
}