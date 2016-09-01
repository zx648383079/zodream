<?php
namespace Zodream\Infrastructure\Database;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/25
 * Time: 9:38
 */
use Zodream\Infrastructure\Base\MagicObject;

abstract class BaseQuery extends MagicObject {

    /**
     * @var Command
     */
    private $_command;

    /**
     * @return Command
     */
    protected function command() {
        if (!$this->_command instanceof Command) {
            $this->_command = Command::getInstance();
        }
        return $this->_command;
    }

    protected function addPrefix($table) {
        return $this->command()->addPrefix($table);
    }

    /**
     * @return string
     */
    abstract public function getSql();

    public function __toString() {
        return $this->getSql();
    }

    public function getError() {
        return $this->command()->getError();
    }
}