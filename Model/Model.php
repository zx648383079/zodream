<?php 
/*********************************
用户表的连接
*********************************/
namespace App\Model;

use App\Lib\Auth;
use App\Lib\Db\DPdo;
use App\Lib\Object\OTime;

abstract class Model extends DPdo{
	/*查询到的数据*/
	protected $models;
	/**
	* 填充数据
	*
	* @return int
	*/
	public function fill()
	{
		$param = func_get_args();
		
		if(count($param) == 1)
		{
			$param=array_shift($param);
		}
		$arr = array_combine($this->fillable,$param);
		
		if(empty($arr['user_id']))
		{
			$arr['user_id'] = Auth::user()->id;
		}
		
		if(empty($arr['udate']))
		{
			$arr['udate'] = OTime::Now();
		}
		if(!isset($arr['cdate']) || empty($arr['cdate']))
		{
			$arr['cdate'] = OTime::Now();
		}
		
		return $this->add($arr);
	}

	/**
	* 根据id 查找值
	*
	* @param $id
	* @return mixed
	*/
	public function findById($id)
	{
		$sql = "SELECT * FROM {$this->table} WHERE id = {$id}";
		return $this->execute($_sql)->fetchObject();
	}
	
	/**
	* 设置bool值
	*
	* @param string $filed
	* @param string $where
	* @return int
	*/
	public function updateBool($filed , $where )
	{
		$sql = "UPDATE {$this->table} SET {$filed} = CASE WHEN {$filed} = 1 THEN 0 ELSE 1 END WHERE ";
		$sql .= $where;
		return $this->execute($_sql)->rowCount();
	}
	
	/**
	* int加
	*
	* @param string $filed
	* @param string $where
	* @param string $num
	* @return int
	*/
	public function updateOne( $filed , $where ,$num = 1)
	{
		$sql = "UPDATE {$this->table} SET {$filed} = {$filed} + {$sum} WHERE ";
		$sql .= $where;
		return $this->execute($_sql)->rowCount();
	}
	
	/**
	* 返回Object
	*
	* @param string $param
	* @param string $filed
	* @return array
	*/
	public function findObject($param = '' , $filed = '*')
	{
		$sql = array(
			'select' => $filed,
			'from' => $this->table
		);
		
		if(!empty($param))
		{
			$sql['where'] = $param;
		}
		return $this->findByHelper( $sql, FALSE);;
	}
	
	/**
	* 返回array
	*
	* @param string $param
	* @param string $filed
	* @return array
	*/
	public function findList($param = '' , $filed = '*')
	{
		$_stmt = $this->findObject($param , $filed);  

		$_result = array(); 
		foreach ($_stmt as $key => $value) {
			foreach ($value as $_key => $_value) {
				$_result[$key][$_key] = $_value;
			}
		}
		return $_result;  
	}
	
	public function assignRow($_key , $_value ,$one = true )
	{
		$arr = $this->findList("{$_key} = '{$_value}'");
			
		if( $one && count($arr) > 0)
		{
			 $arr = $arr[0];
		}
		$this->models = $arr;
	}
	
	public function hasOne($model , $key , $forkey = 'id')
	{
		$table = new $model();
		$table->assignRow($forkey , $this->$key);
		return $table;
	}
	
	public function hasMany($model , $key , $forkey = 'id' )
	{
		$table = new $model();
		$table->assignRow($forkey , $this->$key , FALSE);
		return $table;
	}
	
	/*
	* 魔术变量
	* 指定获取的数据来源
	*
	*
	*/
	public function __get($name)
	{
		if(isset($this->$name))
		{
			return $this->$name;
		}
		else if(isset($this->models[$name])){
			return $this->models[$name];
		}else{
			return null;
		}
	}
}