<?php
namespace Zodream\Service;

use Zodream\Infrastructure\Http\Request;

class Console extends Web {
    public function setPath($path) {
        if (is_null($path)) {
            $path = Request::argv('arguments.0');
        }
        return parent::setPath($path);
    }
}