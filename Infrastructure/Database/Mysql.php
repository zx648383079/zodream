<?php 
namespace Zodream\Infrastructure\Database;
/**
* mysql 
* 
* @author Jason
*/

class Mysql extends Database {
	
	/**
	 * 连接数据库
	 *
	 */
	protected function connect() {
		if (empty($this->configs)) {
			die ('Mysql host is not set');
		}
		$this->driver = mysql_connect(
				$this->configs['host']. ':'. $this->configs['port'], 
				$this->configs['user'], 
				$this->configs['password']
		)
		or die('There was a problem connecting to the database');
		mysql_select_db($this->configs['database'], $this->driver) 
		or die ("Can\'t use {$this->configs['database']} : " . mysql_error());
		if (isset($this->configs['encoding'])) {
			mysql_query('SET NAMES '.$this->configs['encoding'], $this->driver);
		}
	}
	
	public function rowCount() {
		return mysql_affected_rows($this->driver);
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
	 * 返回上一步执行INSERT生成的id
	 *
	 * @access public
	 *
	 */
	public function lastInsertId() {
		return mysql_insert_id($this->driver);
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
		$this->result = mysql_query($sql, $this->driver);
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
		mysql_close($this->driver);
	}
	
	public function getError() {
		return mysql_error($this->driver);
	}
	
	public function __destruct() {
		$this->close();
	}
}