<?php
namespace Zodream\Infrastructure\Disk;

class Stream {
    protected $stream = null;

    protected $useLocking = false;

    protected $file;

    public function __construct($file, $useLocking = false) {
        $this->useLocking = $useLocking;
        if (is_resource($file)) {
            $this->setStream($file);
            return;
        }
        if (!$file instanceof File) {
            $file = new File($file);
        }
        $this->file = $file;
    }

    public function setStream($stream) {
        $this->stream = $stream;
        return $this;
    }

    /**
     *
     * @param string $mode  默认写入   r 为读取
     * @return $this
     */
    public function open($mode = 'a') {
        if (is_resource($this->stream)) {
            return $this;
        }
        $this->stream = fopen($this->file->getFullName(), $mode);
        return $this;
    }

    /**
     * 判断是否已经结束
     * @return bool
     */
    public function isEnd() {
        return feof($this->stream);
    }

    /**
     * 读取一行
     * @return bool|string
     */
    public function readLine() {
        return fgets($this->stream);
    }

    public function write($content) {
        $this->open('a');
        if ($this->useLocking) {
            flock($this->stream, LOCK_EX);
        }
        fwrite($this->stream, (string)$content);
        if ($this->useLocking) {
            flock($this->stream, LOCK_UN);
        }
        return $this;
    }

    public function writeLine($line) {
        return $this->write(PHP_EOL. $line);
    }

    public function read($length) {
        $this->open('r');
        return fread($this->stream, $length);
    }

    public function move($offset, $whence = SEEK_SET) {
        fseek($this->stream, $offset, $whence);
        return $this;
    }

    public function close() {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->stream = null;
        return $this;
    }

}