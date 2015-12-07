<?php
namespace Zodream\Hand\Generate;

use Zodream\Body\Model;
use Zodream\Body\Object\Str;

class Generate extends Model {
	
	public function make() {
		echo 'jjj';
		var_dump($this->getColumn('account'));
		exit();
	}
	
	/**
	 * 获取数据库名
	 */
	public function getDatabase() {
		return $this->db->getArray('SHOW DATABASES');
	}
	
	/**
	 * 获取表明
	 * @param string $arg 数据库名 默认是配置文件中的数据库
	 */
	public function getTable($arg = null) {
		if (!empty($arg)) {
			$this->db->execute('use '.$arg);
		}
		return $this->db->getArray('SHOW TABLES');
	}
	
	/**
	 * 获取列名
	 * @param unknown $arg
	 */
	public function getColumn($arg) {
		$arg = $this->prefix.Str::fReplace($arg, $this->prefix);
		return $this->db->getArray('SHOW COLUMNS FROM '.$arg);
	}
	
}