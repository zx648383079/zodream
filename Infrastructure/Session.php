<?php 
namespace Zodream\Infrastructure;
/**
* session 读写类
* 
* @author Jason
* @time 2015-12-3
*/
class Session {
	public $data = array();
	
	public function __construct() {
		if (!session_id()) {
			ini_set('session.use_cookies', 'On');
			ini_set('session.use_trans_sid', 'Off');
			session_set_cookie_params(0, '/');
			session_save_path(dirname(APP_DIR).'/tmp');
			session_start();
		}
	
		$this->data = & $_SESSION;
	}
	
	/**
	 * 删除
	 * @param string $name
	 */
	public function delete($name) {
		unset($this->data[$name]);
	}
	
	/**
	 * 清空
	 */
	public function clear() {
		session_destroy();
	}
}