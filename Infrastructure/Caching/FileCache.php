<?php 
namespace Zodream\Infrastructure\Caching;
/**
* 文件缓存类
* 
* @author Jason
*/

class FileCache extends Cache {
	
	public $cachePath = 'cache/';
	public $cacheExtension = '.cache';
	/**
	 * gc自动执行的几率 0-1000000；
	 * @var int
	 */
	public $gcChance = 10;
	
	protected function getValue($key) {
		$cacheFile = $this->_getCacheFile($key);
        if (is_file($cacheFile) && @filemtime($cacheFile) > time()) {
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
		$cacheFile = $this->_getCacheFile($key);
		if (@file_put_contents($cacheFile, $value, LOCK_EX) !== false) {
            if ($duration <= 0) {
                $duration = 31536000; // 1 year
            }
            return @touch($cacheFile, $duration + time());
        }
        return null;
	}
	
	protected function addValue($key, $value, $duration) {
		$cacheFile = $this->_getCacheFile($key);
        if (@filemtime($cacheFile) > time()) {
            return false;
        }

        return $this->setValue($key, $value, $duration);
	}
	
	protected function hasValue($key) {
		$cacheFile = $this->_getCacheFile($key);
        return @filemtime($cacheFile) > time();
	}
	
	protected function deleteValue($key) {
		$cacheFile = $this->_getCacheFile($key);
        return @unlink($cacheFile);
	}
	
	protected function clearValue() {
		$this->gc(true, false);
		return true;
	}

    private function _getCacheDir() {
        return APP_DIR.'/'.trim($this->cachePath, '/');
    }
	
	private function _getCacheFile($key) {
        $cache = $this->_getCacheDir();
        if (!is_dir($cache)) {
            mkdir($cache);
        }
		return $cache.'/'.$key.$this->cacheExtension;
	}
	
	public function gc($force = false, $expiredOnly = true) {
        if ($force || mt_rand(0, 1000000) < $this->gcChance) {
            $this->gcRecursive($this->_getCacheDir(), $expiredOnly);
        }
    }
    
    protected function gcRecursive($path, $expiredOnly) {
        if (($handle = opendir($path)) !== false) {
            while (($file = readdir($handle)) !== false) {
                if ($file[0] === '.') {
                    continue;
                }
                $fullPath = $path.'/' . $file;
                if (!$expiredOnly || $expiredOnly && @filemtime($fullPath) < time()) {
                    if (!@unlink($fullPath)) {
                        
                    }
                }
            }
            closedir($handle);
        }
    }
}