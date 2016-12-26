<?php
namespace Zodream\Module\Gzo\Service;

class GenerateController extends Controller {

    public function indexAction() {
        return $this->show('index');
    }

    public function modelAction() {
        return $this->show('model');
    }

    public function crudAction() {
        return $this->show('crud');
    }

    public function controllerAction() {
        return $this->show('controller');
    }


}