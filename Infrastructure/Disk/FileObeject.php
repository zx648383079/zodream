<?php
namespace Zodream\Infrastructure\Disk;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/7/29
 * Time: 11:26
 */
abstract class FileObject {
    
    protected $name;
    
    protected $fullName;

    /**
     * GET FILE/DIRECTORY NAME
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * GET FILE/DIRECTORY FULL NAME
     * @return string
     */
    public function getFullName() {
        return $this->fullName;
    }

    /**
     * EXIST FILE/DIRECTORY
     * @return boolean
     */
    public function exist() {
        return file_exists($this->fullName);
    }

    /**
     * RENAME FILE
     * @param string $file
     * @param resource $context
     * @return bool
     */
    public function rename($file, $context = null) {
        return rename($this->fullName, $file, $context);
    }

    /**
     * SET FILE MODE
     * @param int $mode
     * @return bool
     */
    public function chmod($mode) {
        return chmod($this->fullName, $mode);
    }
    
    abstract public function move($file);
    
    abstract public function copy($file);
    
    abstract public function delete();
    
    public function __toString() {
        return $this->getName();
    }
}