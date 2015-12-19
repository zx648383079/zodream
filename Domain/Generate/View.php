<?php
namespace Zodream\Hand\Generate;

class View {
	private $_name;
	private $_dir;
	private $_column;
	
	public function __construct($name = 'Home', $column = array()) {
		$this->_name = ucfirst($name);
		$this->_column = $column;
		$this->_dir = APP_DIR.(THEME_DIR === '/' ? VIEW_DIR : THEME_DIR);
	}
	
	private function _index() {
		$colum = '';
		foreach ($this->_column as $value) {
			$colum .= '<td>'.ucfirst($value['Field']).'</td>';
		}
		$data = '';
		foreach ($this->_column as $value) {
			$data .= '<td><?php echo $value[\''.$value['Field'].'\'];?></td>';
		}
		
	}
	
	private function _edit() {
		$data = '';
		foreach ($this->_column as $value) {
			if ($value['Extra'] === 'auto_increment') {
				continue;
			}
			$data .= '<div>'.
					 '<lable>'.ucfirst($value['Field']).':</lable>:'.
					 $this->_form($value).
					 '</div>';
		}
	}
	
	private function _view() {
		$data = '';
		foreach ($this->_column as $key => $value) {
			$data .= '<div><lable>'.ucfirst($value['Field']).'</lable>:<?php echo $data[\''.$value['Field'].'\'];?></div>';
		}
	}
	
	private function _form($value) {
		$required = null;
		if ($value['Null'] === 'NO') {
			$required = ' required';
		}
		switch (explode('(', $value['Type'])[0]) {
			case 'int':
			case 'varchar':
			case 'char':
				return '<input type="text" name="'.$value['Field'].'" value="'.$value['Default'].'" '.$required.'>';
				break;
			case 'text': 
				return '<textarea name="'.$value['Field'].'" '.$required.'>'.$value['Default'].'</textarea>';
			default:
				;
			break;
		}
	}
	
	private function _layout() {
		$layout_dir = $this->_dir.'layout/';
		if (!is_dir($layout_dir)) {
			mkdir($layout_dir);
		}
		if (!file_exists($layout_dir.'head.php')) {
			
		}
		if (!file_exists($layout_dir.'foot.php')) {
				
		}
	}
	
}