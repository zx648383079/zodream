<?php
namespace Zodream\Domain\Upload;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/28
 * Time: 18:48
 */
use Zodream\Infrastructure\Request;

class UploadInput extends BaseUpload {

    public function __construct() {
        $this->name = Request::server('HTTP_X_FILENAME');
        $this->setType();
    }

    /**
     * 保存到指定路径
     * @param string $file
     * @return bool
     */
    public function save($file) {
        if (!$this->checkFolder(dirname($file))) {
            return false;
        }
        if (!$fileOpen = @fopen('php://input', 'rb')) {
            $this->setError('INPUT_ERROR');
            return false;
        }
        if (!$fileOutput = @fwrite($file, 'wb')) {
            $this->setError('WRITE_ERROR');
            return false;
        }

        while ($buff = fread($fileOpen, 4096)) {
            fwrite($fileOutput, $buff);
        }

        @fclose($fileOutput);
        @fclose($fileOpen);
        $this->size = filesize($file);
        return true;
    }
}