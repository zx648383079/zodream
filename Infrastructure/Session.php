<?php 
namespace Zodream\Infrastructure;
/**
* session 读写类
* 
* @author Jason
* @time 2015-12-3
*/
use Zodream\Infrastructure\Traits\SingletonPattern;

class Session extends MagicObject {
	
	use SingletonPattern;
	
	protected $lifeTime = 0;
	
	public function __construct() {
		$this->init();
	}
	
	public function init() {
		if (!session_id()) {
			session_name('ZoDream');
			ini_set('session.use_cookies', 'On');
			ini_set('session.use_only_cookies', '1');
			ini_set('session.use_trans_sid', 'Off');
			//session_set_cookie_params($this->lifeTime, '/', null, null, true);
			session_save_path(APP_DIR.'/temp');
			session_start();
		}
		
		$this->_data = & $_SESSION;
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