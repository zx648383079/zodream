<?php
namespace Zodream\Infrastructure\Disk;
/**
 * THIS IS CLASS DIRECTORY,
 *      IS'S EASY TO USE DIRECTORY.
 * User: zx648
 * Date: 2016/7/29
 * Time: 11:30
 */
class Directory extends FileObject {

    public function __construct($directory) {
        $this->fullName = ltrim($directory, '/\\');
        $this->name = basename($this->fullName);
    }

    /**
     * REGEX TO MATCH FILE
     * @param string $pattern
     * @param int $flag
     * @return FileObject[]
     */
    public function glob($pattern = '*', $flag = 0) {
        $files = [];
        foreach (glob($this->fullName.'/'.$pattern, $flag) as $item) {
            if (is_dir($item)) {
                $files[] = new static($item);
                continue;
            }
            $files[] = new File($item);
        }
        return $files;
    }

    /**
     * CREATE DIRECTORY
     * @return bool
     */
    public function create() {
        if ($this->exist()) {
            return true;
        }
        $args = explode('/', str_replace('\\', '/', $this->fullName));
        $directory = '';
        $result = true;
        foreach ($args as $item) {
            $directory .= $$item . '/';
            if (!is_dir($directory)) {
                $result = mkdir($directory);
            }
        }
        return $result;
    }

    /**
     * GET ALL CHILDREN OF DIRECTORY
     * @return FileObject[]
     */
    public function children() {
        $files = [];
        $handle = opendir($this->fullName);
        while (false !== ($name = readdir($handle))) {
            if ($name == '.' || $name == '..') {
                continue;
            }
            $file = $this->fullName.'/'.$name;
            if (is_dir($file)) {
                $files[] = new static($file);
                continue;
            }
            $files[] = new File($file);
        }
        return $files;
    }

    /**
     * GET FILE BY NAME IN THIS DIRECTORY
     * @param string $name
     * @return bool|FileObject
     */
    public function child($name) {
        $file = $this->fullName . '/'. $name;
        if (is_dir($file)) {
            return new static($file);
        }
        if (is_file($file)) {
            return new File($file);
        }
        return false;
    }

    public function hasFile($name) {
        return is_file($this->fullName . '/'. $name);
    }

    public function hasDirectory($name) {
        return is_dir($this->fullName . '/'. $name);
    }

    /**
     * GET FILE BY NAME
     * @param string $name
     * @return File
     */
    public function childFile($name) {
        return new File($this->fullName . '/'. $name);
    }

    /**
     * GET DIRECTORY BY NAME 
     * @param $name
     * @return static
     */
    public function childDirectory($name) {
        return new File($this->fullName . '/'. $name);
    }

    /**
     * EXIST DIRECTORY
     * @return bool
     */
    public function exist() {
        return is_dir($this->fullName);
    }

    /**
     * GET FREE SPACE
     * @return float
     */
    public function freeSpace() {
        return disk_free_space($this->fullName);
    }

    /**
     * GET TOTAL SPACE
     * @return float
     */
    public function totalSpace() {
        return disk_total_space($this->fullName);
    }

    /**
     * ADD FILE IN DIRECTORY
     * @param string $name
     * @param string $data
     * @return int
     */
    public function addFile($name, $data) {
        $file = new File($this->fullName.'/'.$name);
        return $file->write($data);
    }

    /**
     * DELETE FILES IN CHILDREN
     * @param string $arg
     * @return bool
     */
    public function deleteFile($arg) {
        foreach (func_get_args() as $name) {
            (new File($this->fullName.'/'.$name))->delete();
        }
        return true;
    }

    /**
     * DELETE DIRECTORIES IN CHILDREN
     * @param string $arg
     * @return bool
     */
    public function deleteDirectory($arg) {
        foreach (func_get_args() as $name) {
            (new static($this->fullName.'/'.$name))->delete();
        }
        return true;
    }

    /**
     * ADD DIRECTORY IN DIRECTORY
     * @param string $name
     * @param int $mode
     * @return bool
     */
    public function addDirectory($name, $mode = 0777) {
        return mkdir($this->fullName.'/'. $name, $mode);
    }

    /**
     * MOVE TO DIRECTORY
     * @param string $file
     * @return bool
     */
    public function move($file) {
        $result = true;
        foreach ($this->children() as $item) {
            $result = $result && $item->move($file. '/'. $item->getName());
        }
        return $result;
    }

    /**
     * COPY TO DIRECTORY
     * @param string $file
     * @return bool
     */
    public function copy($file) {
        $result = true;
        foreach ($this->children() as $item) {
            $result = $result && $item->copy($file. '/'. $item->getName());
        }
        return $result;
    }

    /**
     * DELETE SELF
     * @return bool
     */
    public function delete() {
        foreach ($this->children() as $item) {
            $item->delete();
        }
        return rmdir($this->fullName);
    }
}