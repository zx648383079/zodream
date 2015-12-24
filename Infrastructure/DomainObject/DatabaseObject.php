<?php
namespace Zodream\Infrastructure\DomainObject;

interface DatabaseObject {
	public static function getInstance(Array $config);
	
	/**
	 * 查询
	 * @param string $sql
	 * @return array
	 */
	public function select($sql);
	
	/**
	 * 插入
	 * @param string $sql
	 * @return integer id
	 */
	public function insert($sql);
	
	/**
	 * 修改
	 * @param string $sql
	 * @return integer 改变的行数
	 */
	public function update($sql);
	
	/**
	 * 删除
	 * @param string $sql
	 * @return integer 删除的行数
	 */
	public function delete($sql);
	
	/**
	 * 获取最后修改的id
	 * @return string
	 */
	public function lastInsertId();
	
	/**
	 * 执行SQL语句
	 *
	 * @access public
	 *
	 * @param array|null $param 条件
	 * @return array 返回查询结果,
	 */
	public function execute($sql);
	
	/**
	 * 得到当前执行语句的错误信息
	 *
	 * @access public
	 *
	 * @return string 返回错误信息,
	 */
	public function getError();
	
	/**
	 * 获取Object结果集
	 * @param string $sql
	 * @return mixed
	 */
	public function getObject($sql);
	
	/**
	 * 获取关联数组
	 * @param string $sql
	 */
	public function getArray($sql);
}