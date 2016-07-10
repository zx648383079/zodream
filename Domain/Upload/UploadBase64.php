<?php
namespace Zodream\Domain\Upload;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/28
 * Time: 14:16
 */
class UploadBase64 extends BaseUpload {

    public function __construct($key = null) {
        $this->load($key);
    }

    public function load($key = null) {
        $this->name = base64_decode($_POST[$key]);
        $this->size = strlen($this->name);
    }

    public function setType($type) {
        $this->type = ltrim($type, '.');
    }

    /**
     * 保存到指定路径
     * @param string $file
     * @return bool
     */
    public function save($file) {
        if (!parent::save($file)) {
            return false;
        }
        if (!file_put_contents($file, $this->name) ||
            !is_file($file)) { //移动失败
            $this->setError('ERROR_WRITE_CONTENT');
            return;
        }
        return true;
    }
}