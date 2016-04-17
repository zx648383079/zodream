<?php
namespace Zodream\Domain\Html;
/**
 * 上传类
 *
 * @author Jason
 * @time 2015-12-1
 */
use Zodream\Infrastructure\Config;

class Upload {
	private $_savePath;          //上传文件保存的路径
	private $_allowType; //设置限制上传文件的类型
	private $_maxSize;           //限制文件上传大小（字节）
	private $_rand;           //设置是否随机重命名文件， false不随机
	
	private $_originName;              //源文件名
	private $_tmpFileName;              //临时文件名
	private $_fileType;               //文件类型(文件后缀)
	private $_fileSize;               //文件大小
	private $_newFileName;              //新文件名
	private $_errorNum  = 0;             //错误号
	private $_errorMessage = '';             //错误报告消息
	
	/**
	 * 公有构造
	 *
	 * @access private
	 *
	 * @param bool $rand 文件命名方式.
	 */
	public function __construct($rand = true) {
		$config          = Config::getInstance()->get('upload');
		$this->_maxSize   = $config['maxsize'];
		$this->setAllowType($config['allowtype']);
		$this->_savePath  = $config['savepath'];
		$this->_rand      = $rand;
	}
	
	public function setAllowType($args) {
		if (is_string($args)) {
			$args = explode(';', $args);
		}
		$this->_allowType = $args;
	}
	
	
	
	/**
	 * 用于设置成员属性（$path, $allowType,$maxsize, $rand）
	 * 可以通过连贯操作一次设置多个属性值
	 *@param  string $key  成员属性名(不区分大小写)
	 *@param  mixed  $val  为成员属性设置的值
	 *@return  object     返回自己对象$this，可以用于连贯操作
	 */
	public function set($key, $val) {
		$key = strtolower($key);
		if (array_key_exists($key, get_class_vars(get_class($this)))) {
			$this->setOption($key, $val);
		}
		return $this;
	}
	
	/**
	 * 调用该方法上传文件
	 * @param $fileField Name 上传文件的表单名称
	 * @return bool 如果上传成功返回数true
	 */
	public function upload($fileField) {
		
		/* 检查文件路径是滞合法 */
		if (!$this->checkFilePath()) {
			$this->_errorMessage = $this->getError();
			return false;
		}
		/* 将文件上传的信息取出赋给变量 */
		$name     = $_FILES[$fileField]['name'];
		$tmpName = $_FILES[$fileField]['tmp_name'];
		$size     = $_FILES[$fileField]['size'];
		$error    = $_FILES[$fileField]['error'];
		
		/* 如果是多个文件上传则$file["name"]会是一个数组 */
		if (is_array($name)) {
			$return = true;
			$errors = array();
			/*多个文件上传则循环处理 ， 这个循环只有检查上传文件的作用，并没有真正上传 */
			for ($i = 0; $i < count($name); $i ++) {
				/*设置文件信息 */
				if ($this->setFiles($name[$i], $tmpName[$i], $size[$i], $error[$i])) {
					if (!$this->checkFileSize() || !$this->checkFileType()) {
						$errors[] = $this->getError();
						$return   = false;
					}
				} else {
					$errors[] = $this->getError();
					$return   = false;
				}
				/* 如果有问题，则重新初使化属性 */
				if (!$return) {
					$this->setFiles();
				}
			}
	
			if ($return) {
				/* 存放所有上传后文件名的变量数组 */
				$fileNames = array();
				/* 如果上传的多个文件都是合法的，则通过销魂循环向服务器上传文件 */
				for ($i = 0; $i < count($name); $i++) {
					if ($this->setFiles($name[$i], $tmpName[$i], $size[$i], $error[$i])) {
						$this->setNewFileName();
						if (!$this->copyFile()) {
							$errors[] = $this->getError();
							$return   = false;
						}
						$fileNames[] = $this->newFileName;
					}
				}
				$this->_newFileName = $fileNames;
			}
			$this->_errorMessage = $errors;
			return $return;
			
		}
		/*上传单个文件处理方法*/
		/* 设置文件信息 */
		if(!$this->setFiles($name, $tmpName, $size, $error)) {
			return $this->setErrorMessage();
		}
		/* 上传之前先检查一下大小和类型 */
		if(!$this->checkFileSize() || !$this->checkFileType()){
			return $this->setErrorMessage();
		}
		/* 为上传文件设置新文件名 */
		$this->setNewFileName();
		/* 上传文件  返回0为成功， 小于0都为错误 */
		if ($this->copyFile()) {
			return true;
		}
		return $this->setErrorMessage();
	}
	
	private function setErrorMessage() {
		$this->_errorMessage = $this->getError();
		return false;
	}
	
	/**
	 * 获取上传后的文件名称
	 *
	 * @return string 上传后，新文件的名称， 如果是多文件上传返回数组
	 */
	public function getFileName() {
		return $this->newFileName;
	}
	
	/**
	 * 上传失败后，调用该方法则返回，上传出错信息
	 *
	 * @return string  返回上传文件出错的信息报告，如果是多文件上传返回数组
	 */
	public function getErrorMsg() {
		return $this->errorMess;
	}

	/**
	 * 设置上传出错信息
	 * @return string
	 */
	private function getError() {
		$str = "上传文件{$this->originName}时出错 : ";
		switch ($this->errorNum) {
			case 4: 
				$str  .= '没有文件被上传'; 
				break;
			case 3: 
				$str  .= '文件只有部分被上传'; 
				break;
			case 2: 
				$str  .= '上传文件的大小超过了HTML表单中MAX_FILE_SIZE选项指定的值'; 
				break;
			case 1: 
				$str  .= '上传的文件超过了php.ini中upload_max_filesize选项限制的值'; 
				break;
			case -1: 
				$str .= '未允许类型'; 
				break;
			case -2: 
				$str .= "文件过大,上传的文件不能超过{$this->maxsize}个字节"; 
				break;
			case -3: 
				$str .= '上传失败'; 
				break;
			case -4: 
				$str .= '建立存放上传文件目录失败，请重新指定上传目录'; 
				break;
			case -5: 
				$str .= '必须指定上传文件的路径'; 
				break;
			default: 
				$str .= '未知错误';
				break;
		}
		return $str.'<br>';
	}

	/**
	 * 设置和$_FILES有关的内容
	 * @param string $name
	 * @param string $tmpName
	 * @param int $size
	 * @param int $error
	 * @return bool
	 */
	private function setFiles($name = '', $tmpName = '', $size = 0, $error = 0) {
		$this->_errorNum = $error;
		if (0 !== $error) {
			return false;
		}
		$this->_originName = $name;
		$this->_tmpFileName = $tmpName;
		$this->_fileType = strtolower(substr(strrchr($name, '.'), 1));
		$this->_fileSize = $size;
		return true;
	}

	/**
	 * 设置上传后的文件名称
	 */
	private function setNewFileName() {
		$this->_newFileName = $this->rand ? $this->proRandName() : $this->originName;
	}

	/**
	 * 检查上传的文件是否是合法的类型
	 * @return bool
	 */
	private function checkFileType() {
		if (in_array(strtolower($this->fileType), $this->allowType)) {
			return true;
		}
		$this->_errorNum = -1;
		return false;
	}

	/**
	 * 检查上传的文件是否是允许的大小
	 * @return bool
	 */
	private function checkFileSize() {
		if ($this->fileSize <= $this->maxsize) {
			return true;
		}
		$this->_errorNum = -2;
		return false;
	}

	/**
	 * 检查是否有存放上传文件的目录
	 * @return bool
	 */
	private function checkFilePath() {
		if (empty($this->path)) {
			$this->_errorNum = -5;
			return false;
		}
		if (file_exists($this->path) && is_writable($this->path)) {
			return true;
		}
		if (@mkdir($this->path, 0755)) {
			return true;
		}
		$this->_errorNum = -4;
		return false;
	}

	/**
	 * 设置随机文件名
	 * @return string
	 */
	private function proRandName() {
		$fileName = date('YmdHis')."_".rand(100, 999);
		return $fileName.'.'.$this->fileType;
	}

	/**
	 * 复制上传文件到指定的位置
	 * @return bool
	 */
	private function copyFile() {
		if ($this->errorNum) {
			return false;
		}
		$path  = rtrim($this->savePath, '/').'/';
		$path .= $this->newFileName;
		if (@move_uploaded_file($this->tmpFileName, $path)) {
			return true;
		}
		$this->_errorNum = -3;
		return false;
	}
}