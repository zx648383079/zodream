<?php
namespace Zodream\Domain\Upload;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/28
 * Time: 10:58
 */
use Zodream\Infrastructure\MagicObject;

class Upload extends MagicObject {
    /** @var BaseUpload[] */
    protected $_data = [];

    protected $folder = null;

    public function setFolder($file) {
        if (empty($file)) {
            return;
        }
        $this->folder = rtrim($file, '/\\'). '/';
    }

    /**
     * 获取
     * @param integer $key
     * @param null $default
     * @return BaseUpload
     */
    public function get($key = 0, $default = null) {
        if (!array_key_exists($key, $this->_data)) {
            return $default;
        }
        return $this->_data[$key];
    }

    public function upload($key) {
        if (!array_key_exists($key, $_FILES)) {
            return false;
        }
        $files = $_FILES[$key];
        if (!is_array($files['name'])) {
            $this->addFile($files);
            return true;
        }
        for ($i = 0, $length = count($files['name']); $i < $length; $i ++) {
            $this->addFile(
                $files['name'][$i], 
                $files['tmp_name'][$i], 
                $files['size'][$i], 
                $files['error'][$i]
            );
        }
    }

    public function addFile($file) {
        if (func_num_args() == 4) {
            $upload = new UploadFile();
            call_user_func_array([$upload, 'load'], func_get_args());
            $this->_data[] = $upload;
            return;
        }
        if ($file instanceof BaseUpload) {
            $this->_data[] = $file;
            return;
        }
        if (is_array($file)) {
            $this->_data[] = new UploadFile($file);
            return;
        }
        if (!is_string($file)) {
            return;
        }
        $this->upload($file);
    }
    
    public function save($folder = null) {
        $this->setFolder($folder);
        $result = true;
        foreach ($this->_data as $item) {
            $result = !$item->save($folder.$item->getRandomName()) || $result;
        }
        return $result;
    }
    
    public function checkType($args, $allow = true) {
        $result = true;
        foreach ($this->_data as $item) {
            $result = !$item->checkType($args, $allow) || $result;
        }
        return $result;
    }

    public function checkSize($min = 10000000, $max = nulll) {
        $result = true;
        foreach ($this->_data as $item) {
            $result = !$item->checkSize($min, $max) || $result;
        }
        return $result;
    }
    
    public function saveOne($file, $index = 0) {
        return $this->_data[$index]->save($file);
    }

    public function getError($index = null) {
        if (!is_null($index)) {
            return $this->_data[$index]->getError();
        }
        $args = [];
        foreach ($this->_data as $item) {
            $args[] = $item->getError();
        }
        return $args;
    }
}