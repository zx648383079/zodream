<?php
namespace Zodream\Domain\Generate;

use Zodream\Domain\Model\Model;
use Zodream\Domain\Response\Redirect;
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Database\Command;
use Zodream\Infrastructure\Factory;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\Template;

class Generate {
	
	protected $template;
	protected $replace = FALSE;
	/**
	 * @var GenerateModel
	 */
	protected $model;
	
	protected $error;

	public function __construct() {
		$this->template = new Template(__DIR__.'/Template/');
	}
	
	public function setReplace($replace = false) {
		$this->replace = $replace;
		return $this;
	}
	
	public function setModel() {
		if (!$this->model instanceof Command) {
			$this->model = new GenerateModel();
		}
	}

	/**
	 * 开始生成的入口
	 */
	public function make() {
		if (!defined('DEBUG') || !DEBUG) {
			Redirect::to('/');
		}
		$this->setModel();
		/*if (Request::isPost()) {
			ResponseResult::sendContentType();
			$this->makeConfig(Request::post());
			$this->importSql(APP_DIR.'/document/zodream.sql');
			Redirect::to('/', 10, '安装完成！');
		}*/
		if (!Request::isPost()) {
			return $this->show('index', [
				'table' => $this->model->getTableByDatabase(),
			]);
		}
		set_time_limit(0);
		echo '自动生成程序启动……<p/>';
		flush();
		ob_flush();
		$table = Request::post('table');
		if (empty($table)) {
			$table = $this->model->getTableByDatabase();
		}
		$mode = Request::post('controller,model,view');
		foreach ($table as $item) {
			$this->generateOne($item, $mode);
			flush();
			ob_flush();
		}
		exit('完成！');
	}

	protected function show($name, $data = []) {
		return Factory::view()->set($data)->setPath(__DIR__.'/View/'.$name.'.php')->render();
	}

	/**
	 * 新建数据库
	 * @param string $name
     * @return boolean
	 */
	public function createDatabase($name) {
		if (empty($name)) {
			return false;
		}
		$this->setModel();
		return $this->model->createDatabase($name);
	}

	/**
	 * 导入SQL文件
	 * @param string $file
	 * @return bool
	 */
	public function importSql($file) {
		if (!is_file($file)) {
			echo $file. '路径不存在！';
			return false;
		}
		$this->setModel();
		$this->model->importSql($file, Config::getValue('db.database'));
		return true;
	}

	/**
	 * 生成一个表
	 * @param string $table
	 * @param array $mode
	 */
	protected function generateOne($table, array $mode) {
		$table = StringExpand::firstReplace($table, $this->model->getPrefix());
		echo '<h3>Table ',$table,'执行开始……</h3><br>';
		$columns = $this->model->getColumn($table);
		if (empty($columns)) {
			echo '<h3>Table ',$table,'为空或不存在！ </h3><br>';
			return;
		}
		$name = $this->getName($table);
		if (!empty($mode['controller']) && $this->makeController($name)) {
			echo $name.' MAKE CONTROLLER SUCCESS ！<br>';
		}
		if (!empty($mode['model']) && $this->makeModel($name, $table, $columns)) {
			echo $name.' MAKE MODEL SUCCESS  ！<br>';
		}
		if (!empty($mode['view']) && $this->makeView($name, $columns)) {
			echo $name.' MAKE VIEW SUCCESS  ！<br>';
		}
		echo '<h3>Table ',$table,'执行成功！</h3><br>';
	}

	/**
	 * 是有 _ 的表名采用驼峰法表示
	 * @param string $table
	 * @return string
	 */
	protected function getName($table) {
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $table)));
	}

	/**
	 * 生成控制器
	 * @param string $name
	 * @return bool
	 */
	public function makeController($name) {
		$this->_baseController();
		$data = array(
			'module' => APP_MODULE,
			'model' => $name.APP_MODEL,
			'name' => $name,
			'form'   => $name.APP_FORM,
			'controller' => $name.APP_CONTROLLER,
			'action' => APP_ACTION,
		);
		return $this->_replace('Controller', $data, APP_DIR.'/Service/'.APP_MODULE.'/'.$data['controller'].'.php');
	}

	/**
	 * 生成基控制器
	 * @return bool
	 */
	private function _baseController() {
		$dir = APP_DIR.'/Service/'.APP_MODULE;
		if (!is_dir($dir)) {
			mkdir($dir);
		}
		return $this->_replace('BaseController', array(
				'module' => APP_MODULE
		), $dir.'/Controller.php');
	}

	/**
	 * 生成数据模型
	 * @param string $name
	 * @param string $table
	 * @param array $columns
	 * @return bool
	 */
	public function makeModel($name, $table, array $columns) {
		$data = $this->_modelFill($columns);
		$data = array(
			'model' => $name.APP_MODEL,
			'table' => $table,
			'data' => $data[1],
			'pk' => $data[0],
			'labels' => $data[2],
			'property' => $data[3],
			'module' => APP_MODULE
		);
		return $this->_replace(
			'Model', $data,
			APP_DIR.'/Domain/Model/'.APP_MODULE.'/'.$data['model'].'.php'
		);
	}

	/**
	 * 生成配置文件
	 * @param array $configs
	 * @param string $module
	 * @return bool
	 */
	public function makeConfig(array $configs, $module = APP_MODULE) {
		if (!is_file($module)) {
			$module = APP_DIR.'/Service/config/'.$module.'.php';
		}
		return $this->_replace(
			'config',
			array('data' => $configs),
			$module
		);
	}

	/**
	 * 生成视图文件
	 * @param string $name
	 * @param array $column
	 */
	public function makeView($name = 'Home', array $column = array()) {
		$dir = APP_DIR.'/UserInterface/'.APP_MODULE;
		if (!is_dir($dir)) {
			mkdir($dir);
		}
		$dir .= '/';
		$this->_viewLayout($dir);
		$viewDir = $dir.$name;
		if (!is_dir($viewDir)) {
			mkdir($viewDir);
		}
		$this->_viewIndex($viewDir, $name, $column);
		$this->_viewEdit($viewDir, $name, $column);
		$this->_viewView($viewDir, $name, $column);
	}

	/**
	 * 生成主视图列表
	 * @param string $dir
	 * @param string $name
	 * @param array $columns
	 * @return bool
	 */
	private function _viewIndex($dir, $name, array $columns) {
		$column = '';
		foreach ($columns as $value) {
			$column .= '			<th>'.ucfirst($value['Field'])."</th>\r\n";
		}
		$data = '';
		foreach ($columns as $value) {
			$data .= '				<td><?=$item[\''.$value['Field']."'];?></td>\r\n";
		}
		return $this->_replace('index', array(
			'data'   => $data,
			'column' => $column,
			'name'   => $name
		), $dir.'/index.php');
	}

	/**
	 * 生成编辑视图
	 * @param string $dir
	 * @param string $name
	 * @param array $columns
	 * @return bool
	 */
	private function _viewEdit($dir, $name, array $columns) {
		$data = '';
		foreach ($columns as $value) {
			if ($value['Extra'] === 'auto_increment') {
				continue;
			}
			$data .= '		'.$this->_viewForm($value)."\r\n";
		}
		return $this->_replace('add', array(
			'data'   => $data,
			'name'   => $name
		), $dir.'/add.php');
	}

	/**
	 * 生成单页查看视图
	 * @param string $dir
	 * @param string $name
	 * @param array $columns
	 * @return bool
	 */
	private function _viewView($dir, $name, array $columns) {
		$data = '';
		foreach ($columns as $key => $value) {
			$data .= '					'.$value['Field'].' => '.ucfirst($value['Field'])."\r\n";
		}
		return $this->_replace('view', array(
			'data'   => $data,
			'name'   => $name
		), $dir.'/view.php');
	}

	/**
	 * 视图中表单的生成
	 * @param $value
	 * @return string
	 */
	private function _viewForm($value) {
		$required = null;
		if ($value['Null'] === 'NO') {
			$required = ", 'required' => true";
		}
		switch (explode('(', $value['Type'])[0]) {
			case 'enum':
				$str = rtrim(substr($value['Type'], strpos($value['Type'], '(')), ')');
				return "->select('{$value['Field']}', [{$str}])";
			case 'text':
				return "->textArea('{$value['Field']}', ['label' => '{$value['Field']}'{$required}])";
			case 'int':
			case 'varchar':
			case 'char':
			default:
				return "->text('{$value['Field']}', ['label' => '{$value['Field']}'{$required}])";
		}
	}

	/**
	 * 生成共享视图
	 * @param string $dir
	 */
	private function _viewLayout($dir) {
		$layout_dir = $dir.'layout';
		if (!is_dir($layout_dir)) {
			mkdir($layout_dir);
		}
		if (!is_file($layout_dir.'head.php')) {
			$this->_replace('head', array(), $layout_dir.'/head.php');
		}
		if (!is_file($layout_dir.'foot.php')) {
			$this->_replace('foot', array(), $layout_dir.'/foot.php');
		}
	}

	/**
	 * 数据模型中的列生成
	 * @param array $columns
	 * @return string
	 */
	private function _modelFill(array $columns) {
		$pk = $data = $labels = $property = [];
		foreach ($columns as $key => $value) {
			$labels[$value['Field']] = ucwords(str_replace('_', ' ', $value['Field']));
			$property[] = '* @property '.
				(stripos($value['Type'], 'int') !== false ||
				stripos($value['Type'], 'bool') !== false ?
					'integer' : 'string').' $'.$value['Field'];
			if ($value['Key'] == 'PRI'
				|| $value['Key'] == 'UNI') {
				$pk[] = $value['Field'];
			}
			if ($value['Extra'] === 'auto_increment') {
				continue;
			}
			$data[$value['Field']] = $this->getValidate($value);
		}
		return [
			$pk,
			$data,
			$labels,
			implode("\r\n", $property)
		];
	}

	protected function getValidate($value) {
		$result = '';
		if ($value['Null'] == 'NO') {
			$result = 'required';
		}
		if ($value['Type'] == 'text') {
			return $result;
		}

		if(!preg_match('#(.+?)\(([0-9]+)\)#', $value['Type'], $match)) {
			return $result;
		}
		switch ($match[1]) {
			case 'int':
				$result .= '|int';
				break;
			case 'tinyint':
				$result .= '|int:0-'.$match[2];
				break;
			case 'char':
			case 'varchar':
			default:
				$result .= '|string:3-'.$match[2];
				break;
		}
		return ltrim($result, '|');
	}

	/**
	 * 替换
	 * @param string $file
	 * @param array $replaces
	 * @param string $output
	 * @return bool
	 */
	private function _replace($file, array $replaces, $output) {
		if (is_file($output) && !$this->replace) {
			$this->error = $output.' 路径已经存在！未使用强制模式！';
			return false;
		}
		if (!is_dir(dirname($output)) && !mkdir(dirname($output))) {
			$this->error = dirname($output). '创建失败！';
			return false;
		}
		$this->template->set($replaces);
		$size = file_put_contents($output, $this->template->getText($file.'.tpl'));
		if ($size <= 0) {
			$this->error = $output. '无法写入!';
			return false;
		}
		return true;
	}
	
	public function getError() {
		return $this->error;
	}

}