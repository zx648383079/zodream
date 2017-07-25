<?php
namespace Zodream\Module\Gzo\Service;

use Zodream\Infrastructure\Disk\File;

class TestController extends Controller {

    public function indexAction() {
        return $this->show('index');
    }

    public function fileAction($file, $className) {
        $file = new File($file);
        if (!$file->exist()) {
            return;
        }
        return $this->show(compact($file, $className));
    }
}