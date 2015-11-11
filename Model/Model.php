<?php 
/*********************************
用户表的连接
*********************************/
namespace App\Model;

use App\Lib\Account;
use App\Lib\Db\DbFactory;
use App\Lib\Object\OTime;
use App\Lib\Object\OArray;

abstract class Model extends DbFactory {
	/*查询到的数据*/
	protected $models;
	/**
	* 填充数据
	*
	* @return int
	*/
	public function fill() {
		$args = func_num_args() > 1 ? func_get_args() : func_get_arg(0);
		$assocArray = OArray::combine($this->fillable, $args);
		if (array_key_exists('account_id', $assocArray) && empty($assocArray['account_id'])) {
			$assocArray['account_id'] = Account::user() === FALSE ? 0 : Account::user()->id;
		}
		if (array_key_exists('udate', $assocArray) && empty($assocArray['udate'])) {
			$assocArray['udate'] = OTime::Now();
		}
		if (array_key_exists('cdate', $assocArray) && empty($assocArray['cdate'])) {
			$assocArray['cdate'] = OTime::Now();
		}
		return $this->add($assocArray);
	}

	/**
	 * 根据id执行更新
	 * @param array $args 需要跟新的数据
	 * @param string|int $id
	 */
	public function updateById($args, $id) {
		$assocArray = OArray::combine( $this->fillable, $args, FALSE);
		if (in_array('udate', $this->fillable)) {
			$assocArray['udate'] = OTime::Now();
		}
		$this->update($assocArray, 'id = '.$id);
	}
	
	
	/**
	* 查询返回Object
	*
	* @param string|array $args Where条件 
	* @param string $filed 需要返回的列
	* @return array
	*/
	public function findObject($args = '', $filed = '*') {
		$sql = array (
			'select' => $filed,
			'from'   => $this->table
		);
		
		if (!empty($args)) {
			$sql['where'] = $args;
		}
		return $this->findByHelper($sql, FALSE);
	}
	
	/**
	* 查询返回array
	*
	* @param string|array $args Where条件 
	* @param string $filed 需要返回的列
	* @return array
	*/
	public function findList($args = '', $filed = '*') {
		$stmt   = $this->findObject($args , $filed);
		$result = array(); 
		foreach ($stmt as $key => $value) {
			foreach ($value as $k => $val) {
				$result[$key][$k] = $val;
			}
		}
		return $result;  
	}
	
	/**
	 * 执行关联查询
	 * @param unknown $key
	 * @param unknown $value
	 * @param string $one
	 */
	public function assignRow($key, $value, $one = true) {
		$assocArray = $this->findList("{$key} = '{$value}'");
		if ( $one && count($assocArray) > 0) {
			 $assocArray = $assocArray[0];
		}
		$this->models = $assocArray;
	}
	
	/**
	 * 一对一关联
	 * @param unknown $model
	 * @param unknown $key
	 * @param string $forkey
	 */
	public function hasOne($model, $key, $forkey = 'id') {
		$table =  new $model();
		$table -> assignRow($forkey, $this->$key);
		return $table;
	}
	
	/**
	 * 一对多关联
	 * @param unknown $model
	 * @param unknown $key
	 * @param string $forkey
	 */
	public function hasMany($model, $key, $forkey = 'id') {
		$table =  new $model();
		$table -> assignRow($forkey, $this->$key, FALSE);
		return $table;
	}
	
	/**
	* 魔术变量
	* 指定获取的数据来源
	*
	*
	*/
	public function __get($name) {
		if (isset($this->$name)) {
			return $this->$name;
		} else if (isset($this->models[$name])) {
			return $this->models[$name];
		} else {
			return null;
		}
	}
}