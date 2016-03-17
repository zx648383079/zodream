<?php
namespace Zodream\Domain\Generate;


use Zodream\Domain\Model;
use Zodream\Domain\Response\Redirect;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\Template;

class Generate {
	
	protected $template;
	protected $replace = FALSE;
	protected $model;

	public function __construct() {
		$this->template = new Template(__DIR__.'/Template/');
		$this->model = new GenerateModel();
	}

	public function make() {
		if (!defined('DEBUG') || !DEBUG) {
			Redirect::to('/');
		}
		echo '自动生成程序启动……<p/>';
		$table = Request::getInstance()->get('table');
		if (empty($table)) {
			$table = $this->model->getTable();
		} else {
			$table = array(strtolower($table));
		}
		$mode = Request::getInstance()->get('mode', 0);
		foreach ($table as $value) {
			$value = StringExpand::firstReplace($value, $this->model->getPrefix());
			echo '<h3>Table ',$value,'执行开始……</h3><br>';
			$columns = $this->model->getColumn($value);
			switch ($mode) {
				case 4:
					$this->makeController($value);
					break;
				case 3:
					$this->makeModel($value, $columns);
					break;
				case 2:
					$this->makeForm($value, $columns);
					break;
				case 1:
					$this->makeView($value, $columns);
					break;
				default:
					echo 'table:指定表； mode:1代表视图！2代表表单！3代表模型！4代表控制器！';
					break;
			}
			echo '<h3>Table ',$value,'执行成功！</h3><br>';
		}
		exit('完成！');
	}

	
	public function makeController($name) {
		$this->_baseController();
		$name = ucfirst(strtolower($name));
		$data = array(
			'module' => APP_MODULE,
			'model' => $name.APP_MODEL,
			'form'   => $name.APP_FORM,
			'controller' => $name.APP_CONTROLLER,
			'action' => APP_ACTION,
		);
		return $this->_replace('Controller', $data, APP_DIR.'/Service/'.APP_MODULE.'/'.$data['controller'].'.php');
	}
	
	private function _baseController() {
		$dir = APP_DIR.'/Service/'.APP_MODULE;
		if (!is_dir($dir)) {
			mkdir($dir);
		}
		return $this->_replace('BaseController', array(
				'module' => APP_MODULE
		), $dir.'/Controller.php');
	}
	
	public function makeModel($name, array $columns) {
		$name = strtolower($name);
		$data = array(
			'model' => ucfirst($name).APP_MODEL,
			'name' => $name,
			'data' => $this->_modelFill($columns),
			'module' => APP_MODULE
		);
		return $this->_replace('Model', $data, APP_DIR.'/Domain/Model/'.APP_MODULE.'/'.$data['model'].'.php');
	}
	
	public function makeForm($name, array $columns) {
		$name = ucfirst(strtolower($name));
		list($column, $datas) = $this->_formColumn($columns);
		$data = array(
			'model'  => $name.APP_MODEL,
			'form'   => $name.APP_FORM,
			'colums' => $column,
			'data'   => $datas,
			'module' => APP_MODULE
		);
		return $this->_replace('Form', $data, APP_DIR.'/Domain/Form/'.APP_MODULE.'/'.$data['form'].'.php');
	}
	
	public function makeConfig(array $configs, $module = APP_MODULE) {
		return $this->_replace('config', array('data' => var_export($configs, true)), APP_DIR.'/Service/config/'.$module.'.php');
	}
	
	public function makeView($name = 'Home', array $column = array()) {
		$dir = APP_DIR.'/UserInterface/'.APP_MODULE;
		if (!is_dir($dir)) {
			mkdir($dir);
		}
		$dir .= '/';
		$this->_viewLayout($dir);
		$name = strtolower($name);
		$viewDir = $dir.ucfirst($name);
		if (!is_dir($viewDir)) {
			mkdir($viewDir);
		}
		$this->_viewIndex($viewDir, $name, $column);
		$this->_viewEdit($viewDir, $name, $column);
		$this->_viewView($viewDir, $name, $column);
	}
	
	private function _viewIndex($dir, $name, array $columns) {
		$colum = '';
		foreach ($columns as $value) {
			$colum .= '<td>'.ucfirst($value['Field']).'</td>';
		}
		$data = '';
		foreach ($columns as $value) {
			$data .= '<td><?php echo $value[\''.$value['Field'].'\'];?></td>';
		}
		return $this->_replace('index', array(
				'data'   => $data,
				'column' => $colum,
				'name'   => $name
		), $dir.'/index.php');
	}
	
	private function _viewEdit($dir, $name, array $columns) {
		$data = '';
		foreach ($columns as $value) {
			if ($value['Extra'] === 'auto_increment') {
				continue;
			}
			$data .= '<div>'.
					'<lable>'.ucfirst($value['Field']).':</lable>:'.
					$this->_viewForm($value).
					'</div>';
		}
		return $this->_replace('edit', array(
				'data'   => $data,
				'name'   => $name
		), $dir.'/edit.php');
	}
	
	private function _viewView($dir, $name, array $columns) {
		$data = '';
		foreach ($columns as $key => $value) {
			$data .= '<div><lable>'.ucfirst($value['Field']).'</lable>:<?php echo $data[\''.$value['Field'].'\'];?></div>';
		}
		return $this->_replace('view', array(
				'data'   => $data,
				'name'   => $name
		), $dir.'/view.php');
	}
	
	private function _viewForm($value) {
		$required = null;
		if ($value['Null'] === 'NO') {
			$required = ' required';
		}
		switch (explode('(', $value['Type'])[0]) {
			case 'int':
			case 'varchar':
			case 'char':
				return '<input type="text" name="'.$value['Field'].'" value="<?php $this->ech(\'data.'.$value['Field'].'\', \''.$value['Default'].'\');?>" '.$required.'>';
				break;
			case 'text':
				return '<textarea name="'.$value['Field'].'" '.$required.'><?php $this->ech(\'data.'.$value['Field'].'\', \''.$value['Default'].'\');?></textarea>';
			default:
				return '<input type="text" name="'.$value['Field'].'" value="<?php $this->ech(\'data.'.$value['Field'].'\', \''.$value['Default'].'\');?>" '.$required.'>';
				break;
		}
	}
	
	private function _viewLayout($dir) {
		$layout_dir = $dir.'layout';
		if (!is_dir($layout_dir)) {
			mkdir($layout_dir);
		}
		if (!file_exists($layout_dir.'head.php')) {
			$this->_replace('head', array(), $layout_dir.'/head.php');
		}
		if (!file_exists($layout_dir.'foot.php')) {
			$this->_replace('foot', array(), $layout_dir.'/foot.php');
		}
	}
	
	private function _modelFill(array $columns) {
		$data = '';
		foreach ($columns as $key => $value) {
			if ($value['Extra'] === 'auto_increment') {
				continue;
			}
			$data .= "\t\t'{$value['Field']}'";
			if ($key < count($columns)-1) {
				$data .= ",\r\n";
			}
		}
		return $data;
	}
	
	private function _formColumn(array $columns) {
		$column = $data = '';
		foreach ($columns as $key => $value) {
			if ($value['Extra'] === 'auto_increment') {
				continue;
			}
			$column .= $value['Field'];
			$data .= "\t\t\t'{$value['Field']}' => 'required'";
			if ($key < count($columns)-1) {
				$column .= ',';
				$data .= ",\r\n";
			}
		}
		return array(
				$column,
				$data
		);
	}
	
	private function _replace($file, array $replaces, $output) {
		if (is_file($output) && !$this->replace) {
			echo $output,' 路径已经存在！未使用强制模式！<br>';
			return false;
		}
		if (!is_dir(dirname($output))) {
			mkdir(dirname($output));
		}
		$this->template->set($replaces);
		file_put_contents($output, $this->template->getText($file.'.tpl'));
		echo $output, ' 生成成功！<br>';
		return true;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}