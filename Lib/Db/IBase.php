<?php 
namespace App\Lib\Db;

/**
* 数据库的接口
*/

interface IBase
{
	/**
	* 查询一条数据
	*
	* @param array|string $param 语句数组
	* @param array|string $where 返回类型
	*/
	function findByHelper($param , $kind);	

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