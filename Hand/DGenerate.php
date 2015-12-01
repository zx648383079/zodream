<?php
namespace App\Lib\Db;

use App\Lib\File\FBase;
use App\Lib\Object\OString;
class DGenerate extends DbFactory {
	
	public function make() {
		return $this->_getColumn('zx_users');
	}
	
	/**
	 * 生成 模型
	 * @param unknown $table
	 * @param unknown $data
	 */
	public function makeModel($table, $data) {
		$content = FBase::reader('Model/TempletModel.php');
		$table   = OString::fReplace($table, $this->db->prefix);
		$name    = ucfirst($table);
		$content = str_replace('namespace App\\Model;', 'namespace '.APP_MODULE.'\\Model;', $content);
		$content = str_replace('TempletModel', $name.APP_MODEL, $content);
		$content = str_replace('$table = \'templet\';', '$table = \''.$table.'\';', $content);
		$fill    = '$fillable = array (';
		foreach ($data as $value) {
			if ($value['Extra'] != 'auto_increment') {
				$fill .= "\r\n\t\t'".$value['Field']."',";
			}
		}
		$fill    = rtrim($fill, ',');
		$fill   .= "\r\n\t);";
		$content = str_replace('$fillable = array ();', $fill, $content);
		$file    = APP_DIR.'/Model/'.$name.APP_MODEL.'.php';
		FBase::writer($file, $content);
	}
	
	/**
	 * 生成控制器
	 * @param unknown $table
	 * @param unknown $data
	 */
	public function makeController($table, $data) {
		$content = FBase::reader('Controller/TempletController.php');
		$table   = OString::fReplace($table, $this->db->prefix);
		$name    = ucfirst($table);
		$content = str_replace('namespace App\\Controller;', 'namespace '.APP_MODULE.'\\Controller;', $content);
		$content = str_replace('App\\Model\\TempletModel', APP_MODULE.'\\Model\\'.$name.APP_MODEL, $content);
		$content = str_replace('TempletController', $name.APP_CONTROLLER, $content);
		$content = str_replace('Action', APP_ACTION, $content);
		$content = str_replace('TempletModel', $name.APP_MODEL, $content);
		$content = str_replace('templet', $table, $content);
		
		$post    = '$request->post(';
		foreach ($data as $value) {
			if ($value['Extra'] != 'auto_increment') {
				$post .= '\''.$value['Field'].'\',';
			}
		}
		$post    = rtrim($post, ',');
		$content = str_replace('$request->post(', $post, $content);
		
		$file    = APP_DIR.'/Controller/'.$name.APP_CONTROLLER.'.php';
		FBase::writer($file, $content);
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
		$arg = $this->db->prefix.OString::fReplace($arg, $this->db->prefix);
		return $this->db->getArray('SHOW COLUMNS FROM '.$arg);
	}
	
}