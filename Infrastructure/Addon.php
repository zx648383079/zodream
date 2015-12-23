<?php
namespace Zodream\Infrastructure;
/**
 * 插件类
 * @author better <einsqing@gmail.com>
 */
abstract class Addon extends Controller
{
	/**
	 * 视图实例对象
	 * @var view
	 * @access protected
	 */
	protected $view = null;

	public $addon_path = '';
	public $config_file = '';
	public $view_path = '';

	public function __construct()
	{
		$this->view = \Think\Think::instance('Think\View');
		$this->addon_path = ADDON_PATH . '/' . $this->getName() . '/';
		//重置视图配置
		C('DEFAULT_THEME', '');
		C('VIEW_PATH', '');
		if (is_file($this->addon_path . 'Conf/config.php')) {
			$this->config_file = $this->addon_path . 'Conf/config.php';
			$config = require $this->config_file;
			C($config);
		}

		$this->view_path = __ROOT__ . '/' . ADDON_PATH . '/' . $this->getName() . '/';
		C("TMPL_PARSE_STRING", array(
				'__IMG__' => $this->view_path . 'View' . C("DEFAULT_THEME") . '/Public/image',
				'__CSS__' => $this->view_path . 'View' . C("DEFAULT_THEME") . '/Public/css',
				'__JS__' => $this->view_path . 'View' . C("DEFAULT_THEME") . '/Public/js',
				'__ADDON_PUBLIC__' => $this->view_path . 'View' . C("DEFAULT_THEME") . '/Public',
		));
	}

	/**
	 * 模板主题设置
	 * @access protected
	 * @param string $theme 模版主题
	 * @return Action
	 */
	final protected function theme($theme)
	{
		$this->view->theme($theme);
		return $this;
	}

	//显示方法
	final protected function display($template = '')
	{
		if ($template == '')
			$template = CONTROLLER_NAME;
			$action = ACTION_NAME;

			echo($this->fetch($template, $action));
	}

	/**
	 * 模板变量赋值
	 * @access protected
	 * @param mixed $name 要显示的模板变量
	 * @param mixed $value 变量的值
	 * @return Action
	 */
	final protected function assign($name, $value = '')
	{
		$this->view->assign($name, $value);
		return $this;
	}


	//用于显示模板的方法
	final protected function fetch($templateFile = CONTROLLER_NAME, $action = ACTION_NAME)
	{
		if (!is_file($templateFile)) {
			if (C('VIEW_PATH')) {
				$templateFile = C('VIEW_PATH') . C('DEFAULT_THEME') . '/' . $templateFile . '/' . $action . C('TMPL_TEMPLATE_SUFFIX');
			} else {
				$templateFile = $this->addon_path . 'View/' . C('DEFAULT_THEME') . '/' . $templateFile . '/' . $action . C('TMPL_TEMPLATE_SUFFIX');
			}

			if (!is_file($templateFile)) {
				throw new \Exception("模板不存在:$templateFile");
			}
		}
		return $this->view->fetch($templateFile);
	}

	final public function getName()
	{
		$class = get_class($this);

		$str = explode('\\', $class);
		return $str[1];
	}


	//必须实现安装
	abstract public function install();

	//必须卸载插件方法
	abstract public function uninstall();

}

//核心方法

/**
 * 执行SQL文件
*/
function execute_sql_file($sql_path)
{
	// 读取SQL文件
	$sql = wp_file_get_contents($sql_path);
	$sql = str_replace("\r", "\n", $sql);
	$sql = explode(";\n", $sql);

	// 替换表前缀
	$orginal = 'wp_';
	$prefix = C('DB_PREFIX');
	$sql = str_replace("{$orginal}", "{$prefix}", $sql);

	// 开始安装
	foreach ($sql as $value) {
		$value = trim($value);
		if (empty ($value))
			continue;

			$res = M()->execute($value);
			// dump($res);
			// dump(M()->getLastSql());
	}
}

// 防超时的file_get_contents改造函数
function wp_file_get_contents($url)
{
	$context = stream_context_create(array(
			'http' => array(
					'timeout' => 30
			)
	)); // 超时时间，单位为秒

	return file_get_contents($url, 0, $context);
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 * @author better
 * @useage u_addons('apply://App/Index/addorder',array('id'=>'1'))
 */
function u_addons($url, $param = array()){
	$url = explode('://', $url);
	$addon = $url[0];
	$url = $url[1];

	$url = U($url, $param, false);
	return $url . '/addon/' . $addon;
}