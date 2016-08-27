<?php 
namespace Zodream\Infrastructure;

/**
* 日志类
* 
* @author Jason
*/
use Zodream\Infrastructure\Disk\File;
use Zodream\Infrastructure\Event\Action;
use Zodream\Infrastructure\ObjectExpand\TimeExpand;

class Log {
	public static $fileHandlerCache;
    private static $initFlag = false;
    private static $MAX_LOOP_COUNT = 3;

    protected $log = array();
 
    private static function init() {
        self::$initFlag = true;
        register_shutdown_function(__NAMESPACE__.'\Log::_shutDown');
    }

    static function record($message, $level = self::ERR, $record = false) {
        if($record) {
            $now = TimeExpand::format();
            self::$log[] = "{$now} ".$_SERVER['REQUEST_URI']." | {$level}: {$message}\r\n";
        }
    }

    public static function save($data, $action) {
        $instance = Config::getInstance()->get('safe.log');
        return (new Action($instance))->run([$data, $action]);
    }
 
    /**
     * 输出到文件日志
     * @param string|File $file 文件路径
     * @param mixed $msg  日志信息
     * @return int
     */
    public static function out($file, $msg) {
        if (!$file instanceof File) {
            $file = Factory::root()->childFile('log/'.ltrim($file, '/'));
        }
        if ($file->getDirectory()->exist()) {
            return $file->write($msg, FILE_APPEND | LOCK_EX);
        }
        return error_log($msg);
        //return self::internalOut($filePath, $msg);
    }
 
    /**
     * @param $filePath
     * @param $msg
     * @param $loop
     * @return int
     */
    private static function internalOut($filePath, $msg, $loop = 0) {
        //以防一直添加失败造成死循环
        if ($loop > self::$MAX_LOOP_COUNT) {
            $result = 0;
        } else {
            $loop++;
            $fp = self::$fileHandlerCache["$filePath"];
            if (empty($fp)) {
                $fp = fopen($filePath, "a+");
                self::$fileHandlerCache[$filePath] = $fp;
            }
            if (flock($fp, LOCK_EX)) {
                $result = fwrite($fp, $msg);
                flock($fp, LOCK_UN);
            } else {
                $result = self::internalOut($filePath, $msg, $loop);
            }
        }
        return $result;
    }
 
    private static function _shutDown() {
        if (!empty(self::$fileHandlerCache)) {
            if (is_array(self::$fileHandlerCache)) {
                foreach (self::$fileHandlerCache as $k => $v) {
                    if (is_resource($v))
                        //file_put_contents("close.txt",$k);
                        fclose($v);
                }
            }
        }
    }
}