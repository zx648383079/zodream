<?php 
namespace Zodream\Infrastructure\Caching;
/**
* 文件缓存类
* 
* @author Jason
*/
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Disk\Directory;
use Zodream\Infrastructure\Disk\File;

class FileCache extends Cache {

    /**
     * @var Directory
     */
	protected $directory;

    protected $configs = [
        'directory' => 'cache/',
        'extension' => '.cache',
        'gc' => 10
    ];


    public function __construct() {
        $this->loadConfigs();
        $this->setDirectory($this->configs['directory']);
    }

    public function setDirectory($directory) {
        if (!$directory instanceof Directory) {
            $directory = new Directory(APP_DIR.'/'.trim($directory, '/'));
        }
        $this->directory = $directory;
        return $this;
    }

    protected function getValue($key) {
		$cacheFile = $this->getCacheFile($key);
        if ($cacheFile->exist() && $cacheFile->modifyTime() > time()) {
            $fp = @fopen($cacheFile, 'r');
            if ($fp !== false) {
                @flock($fp, LOCK_SH);
                $cacheValue = @stream_get_contents($fp);
                @flock($fp, LOCK_UN);
                @fclose($fp);
                return $cacheValue;
            }
        }
        return false;
	}
	
	protected function setValue($key, $value, $duration) {
		$this->gc();
		$cacheFile = $this->getCacheFile($key);
		if ($cacheFile->write($value, LOCK_EX) !== false) {
            if ($duration <= 0) {
                $duration = 31536000; // 1 year
            }
            return $cacheFile->touch($duration + time());
        }
        return null;
	}
	
	protected function addValue($key, $value, $duration) {
		$cacheFile = $this->getCacheFile($key);
        if ($cacheFile->modifyTime() > time()) {
            return false;
        }

        return $this->setValue($key, $value, $duration);
	}
	
	protected function hasValue($key) {
		$cacheFile = $this->getCacheFile($key);
        return !$cacheFile->exist() || $cacheFile->modifyTime() > time();
	}
	
	protected function deleteValue($key) {
		$cacheFile = $this->getCacheFile($key);
        return $cacheFile->delete();
	}
	
	protected function clearValue() {
		$this->gc(true, false);
		return true;
	}

    /**
     * @param string $key
     * @return File
     */
	public function getCacheFile($key) {
        $this->directory->create();
		return $this->directory->childFile($key.$this->configs['extension']);
	}
	
	public function gc($force = false, $expiredOnly = true) {
        if ($force || mt_rand(0, 1000000) < $this->getGC()) {
            $this->gcRecursive($this->directory, $expiredOnly);
        }
    }
    
    protected function gcRecursive(Directory $directory, $expiredOnly) {
        foreach ($directory->children() as $item) {
            if (!$expiredOnly || $expiredOnly 
                && $item instanceof File 
                && $item->modifyTime() < time()) {
                $item->delete();
            }
        }
    }
}