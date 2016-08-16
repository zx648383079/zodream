<?php
namespace Zodream\Infrastructure\Disk;
/**
 * THIS IS CLASS FILE
 *      MAKE YOU FEEL EASY.
 * User: zx648
 * Date: 2016/7/29
 * Time: 11:28
 */
class File extends FileObject {

    /**
     * @var string EXTENSION (NO POINT)
     */
    protected $extension;

    /**
     * @var string PARENT DIRECTORY (FULL NAME)
     */
    protected $directory;

    public function __construct($file) {
        $this->fullName = $file;
        $args = pathinfo($this->fullName);
        $this->name = $args['basename'];
        $this->extension = $args['extension'];
        $this->directory = $args['dirname'];
    }
    
    public function getExtension() {
        return $this->extension;
    }

    /**
     * GET FILE TYE
     * @return string
     */
    public function type() {
        return filetype($this->fullName);
    }

    /**
     * GET FILE MIME
     * @return string|bool
     */
    public function mimeType() {
        if (!class_exists('finfo')) {
            return false;
        }
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->fullName);
    }

    /**
     * GET A INSTANCE OF PARENT DIRECTORY
     * @return Directory
     */
    public function getDirectory() {
        return new Directory($this->directory);
    }

    /**
     * GET A NAME OF PARENT DIRECTORY
     * @return string
     */
    public function getDirectoryName() {
        return $this->directory;
    }

    /**
     * @return int
     */
    public function size() {
        return filesize($this->fullName);
    }

    /**
     * LAST ACCESS TIME
     * @return int
     */
    public function accessTime() {
        return fileatime($this->fullName);
    }

    /*
     * CREATE FILE TIME
     * @return int
     */
    public function createTime() {
        return filectime($this->fullName);
    }

    /**
     * UPDATE FILE TIME
     * @return int
     */
    public function modifyTime() {
        return filemtime($this->fullName);
    }

    /**
     * FILE EXIST
     * @return bool
     */
    public function exist() {
        return is_file($this->fullName);
    }

    /**
     * IT'S EXECUTABLE
     * @return bool
     */
    public function canExecute() {
        return is_executable($this->fullName);
    }

    /**
     * IT'S READABLE
     * @return bool
     */
    public function canRead() {
        return is_readable($this->fullName);
    }

    /**
     * IT'S WRITABLE
     * @return bool
     */
    public function canWrite() {
        return is_writable($this->fullName);
    }

    /**
     * UPDATE FILE MODIFY TIME AND ACCESS TIME
     *      IF NOT EXIST, WILL CREATE    
     * @param int $modifyTime
     * @param int $accessTime
     * @return bool
     */
    public function touch($modifyTime = null, $accessTime = null) {
        return touch($this->fullName, $modifyTime, $accessTime);
    }

    /**
     * GET FILE CONTENT
     * @return string
     */
    public function read() {
        return file_get_contents($this->fullName);
    }

    /**
     * PUT FILE CONTENT
     * @param string $data
     * @param bool $lock
     * @return int
     */
    public function write($data, $lock = false) {
        if (!is_string($data) && !is_integer($data)) {
            $data = var_export($data, true);
        }
        return file_put_contents($this->fullName, $data, $lock ? LOCK_EX : 0);
    }

    /**
     * APPEND FILE
     * @param string $data
     * @return int
     */
    public function append($data) {
        return file_put_contents($this->fullName, $data, FILE_APPEND);
    }

    /**
     * PREPEND FILE
     * @param string $data
     * @return int
     */
    public function prepend($data) {
        if ($this->exist()) {
            return $this->write($data.$this->read());
        }
        return $this->write($data);
    }

    /**
     * MOVE FIFE TO
     * @param string $file
     * @return bool
     */
    public function move($file) {
        return $this->rename($file);
    }

    /**
     * COPY FILE TO
     * @param string $file
     * @return bool
     */
    public function copy($file) {
        return copy($this->fullName, $file);
    }

    /**
     * DELETE FILE SELF
     * @return bool
     */
    public function delete() {
        return unlink($this->fullName);
    }

    /**
     * GET FILE MD5
     * @return string
     */
    public function md5() {
        return md5_file($this->fullName);
    }
}