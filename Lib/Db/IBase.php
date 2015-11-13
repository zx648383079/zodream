<?php
namespace App\Lib\Db;

/**
* 数据库的接口
*/

interface IBase {
	
	/**
	 * 查询
	 * @param unknown $sql
	 */
	function select($sql);
	
	/**
	 * 插入
	 * @param unknown $sql
	 */
	function insert($sql);
	
	/**
	 * 修改
	 * @param unknown $sql
	 */
	function update($sql);
	
	/**
	 * 删除
	 * @param unknown $sql
	 */
	function delete($sql);

	/**
	* 执行数据库语句
	*
	* @param string $sql 语句
	*/
	function execute($sql);
	
	/**
	* 获取错误信息
	*
	*/
	function getError();
	
	/**
	 * 获取结果集的Object
	 */
	function getObject($sql);
	
	/**
	 * 获取结果集的关联数组
	 */
	function getArray($sql);
}