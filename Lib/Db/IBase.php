<?php 
namespace App\Lib\Db;

/**
* 数据库的接口
*/

interface IBase
{
	
	/**
	* 增加数据
	*
	* @param array $param 增加数据的键值对
	*/
	function add($param);
	
	/**
	* 修改数据
	*
	* @param array $param 修改数据的键值对
	* @param array|string $where 修改数据的条件
	*/
	function update($param , $where);
	
	/**
	* 删除数据
	*
	* @param array|string $where 删除数据的条件
	*/
	function delete($where);
	
	/**
	* 查询数据
	*
	* @param array|string $where 数据的条件
	* @param array|string $filed 要的值
	*/
	function find($where , $filed);
	
	/**
	* 查询一条数据
	*
	* @param array|string $where 数据的条件
	*/
	function findOne($where);	
	
	/**
	* 查询一条数据
	*
	* @param array|string $param 语句数组
	* @param array|string $where 返回类型
	*/
	function findByHelper($param , $kind);	
	
	/**
	* 查询返回array
	*
	*/
	function getList();
	
	/**
	* 查询返回object
	*
	*/
	function getObject();
	
	/**
	* 查询计数
	*	
	* @param array|string $where 数据的条件
	*/
	function count($where);
	
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
}