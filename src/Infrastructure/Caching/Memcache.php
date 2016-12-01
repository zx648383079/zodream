<?php
namespace Zodream\Infrastructure\Caching;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/17
 * Time: 22:43
 */

/**
 * PHP memcache 队列类
 * @author LKK/lianq.net
 * @version 0.3
 * @修改说明:
 * 1.放弃了之前的AB面轮值思路,使用类似数组的构造,重写了此类.
 * 2.队列默认先进先出,但增加了反向读取功能.
 * 3.感谢网友FoxHunter提出的宝贵意见.
 * @example:
 * $obj = new memcacheQueue('duilie');
 * $obj->add('1asdf');
 * $obj->getQueueLength();
 * $obj->read(10);
 * $obj->get(8);
 */
class Memcache {
    public static $client;   //memcache客户端连接
    public   $access;   //队列是否可更新
    private   $expire;   //过期时间,秒,1~2592000,即30天内
    private   $sleepTime;   //等待解锁时间,微秒
    private   $queueName;   //队列名称,唯一值
    private   $retryNum;   //重试次数,= 10 * 理论并发数
    public   $currentHead;  //当前队首值
    public   $currentTail;  //当前队尾值

    const MAXNUM  = 20000;    //最大队列数,建议上限10K
    const HEAD_KEY = '_lkkQueueHead_';  //队列首kye
    const TAIL_KEY = '_lkkQueueTail_';  //队列尾key
    const VALU_KEY = '_lkkQueueValu_';  //队列值key
    const LOCK_KEY = '_lkkQueueLock_';  //队列锁key

    /**
     * 构造函数
     * @param string $queueName  队列名称
     * @param int $expire 过期时间
     * @param array $config  memcache配置
     *
     */
    public function __construct($queueName = '', $expire = 0, $config = array()) {
        if (empty($config)) {
            self::$client = memcache_pconnect('127.0.0.1',11211);
        } elseif (is_array($config)) {//array('host'=>'127.0.0.1','port'=>'11211')
            self::$client = memcache_pconnect($config['host'],$config['port']);
        } elseif (is_string($config)) {//"127.0.0.1:11211"
            $tmp = explode(':',$config);
            $conf['host'] = isset($tmp[0]) ? $tmp[0] : '127.0.0.1';
            $conf['port'] = isset($tmp[1]) ? $tmp[1] : '11211';
            self::$client = memcache_pconnect($conf['host'],$conf['port']);
        }
        if (!self::$client) {
            return false;
        }

        ignore_user_abort(true);//当客户断开连接,允许继续执行
        set_time_limit(0);//取消脚本执行延时上限

        $this->access = false;
        $this->sleepTime = 1000;
        $expire = empty($expire) ? 3600 : intval($expire) + 1;
        $this->expire = $expire;
        $this->queueName = $queueName;
        $this->retryNum = 1000;

        $this->head_key = $this->queueName . self::HEAD_KEY;
        $this->tail_key = $this->queueName . self::TAIL_KEY;
        $this->lock_key = $this->queueName . self::LOCK_KEY;

        $this->_initSetHeadNTail();
    }

    /**
     * 初始化设置队列首尾值
     */
    private function _initSetHeadNTail() {
        //当前队列首的数值
        $this->currentHead = memcache_get(self::$client, $this->head_key);
        if($this->currentHead === false) $this->currentHead =0;

        //当前队列尾的数值
        $this->currentTail = memcache_get(self::$client, $this->tail_key);
        if($this->currentTail === false) {
            $this->currentTail =0;
        }
    }

    /**
     * 当取出元素时,改变队列首的数值
     * @param int $step 步长值
     */
    private function _changeHead($step = 1) {
        $this->currentHead += $step;
        memcache_set(self::$client, $this->head_key, $this->currentHead, false, $this->expire);
    }

    /**
     * 当添加元素时,改变队列尾的数值
     * @param int $step 步长值
     * @param bool $reverse 是否反向
     * @return null
     */
    private function _changeTail($step = 1, $reverse = false){
        if (!$reverse) {
            $this->currentTail += $step;
        }else{
            $this->currentTail -= $step;
        }
        memcache_set(self::$client, $this->tail_key, $this->currentTail, false, $this->expire);
    }

    /**
     * 队列是否为空
     * @return bool
     */
    private function _isEmpty(){
        return (bool)($this->currentHead === $this->currentTail);
    }

    /**
     * 队列是否已满
     * @return bool
     */
    private function _isFull(){
        $len = $this->currentTail - $this->currentHead;
        return (bool)($len === self::MAXNUM);
    }

    /**
     * 队列加锁
     */
    private function _getLock(){
        if ($this->access === false) {
            $i = 0;
            while (!memcache_add(self::$client, $this->lock_key, 1, false, $this->expire)) {
                usleep($this->sleepTime);
                $i ++;
                if ($i > $this->retryNum) {//尝试等待N次
                    return false;
                    break;
                }
            }

            $this->_initSetHeadNTail();
            return $this->access = true;
        }

        return $this->access;
    }

    /**
     * 队列解锁
     */
    private function _unLock() {
        memcache_delete(self::$client, $this->lock_key, 0);
        $this->access = false;
    }

    /**
     * 获取当前队列的长度
     * 该长度为理论长度,某些元素由于过期失效而丢失,真实长度<=该长度
     * @return int
     */
    public function getQueueLength() {
        $this->_initSetHeadNTail();
        return intval($this->currentTail - $this->currentHead);
    }

    /**
     * 添加队列数据
     * @param void $data 要添加的数据
     * @return bool
     */
    public function add($data) {
        if (!$this->_getLock()) {
            return false;
        }

        if ($this->_isFull()) {
            $this->_unLock();
            return false;
        }

        $value_key = $this->queueName . self::VALU_KEY . strval($this->currentTail +1);
        $result = memcache_set(
            self::$client, $value_key, $data, MEMCACHE_COMPRESSED, $this->expire);
        if ($result) {
            $this->_changeTail();
        }

        $this->_unLock();
        return $result;
    }

    /**
     * 读取队列数据
     * @param int $length 要读取的长度(反向读取使用负数)
     * @return array
     */
    public function read($length = 0) {
        if (!is_numeric($length)) {
            return false;
        }
        $this->_initSetHeadNTail() ;

        if ($this->_isEmpty()) {
            return false;
        }

        if (empty($length)) {
            $length = self::MAXNUM;
        }//默认所有
        $keyArr = array();
        if ($length > 0) {//正向读取(从队列首向队列尾)
            $tmpMin = $this->currentHead;
            $tmpMax = $tmpMin + $length;
            for($i= $tmpMin; $i<=$tmpMax; $i++){
                $keyArr[] = $this->queueName . self::VALU_KEY . $i;
            }
        } else {//反向读取(从队列尾向队列首)
            $tmpMax = $this->currentTail;
            $tmpMin = $tmpMax + $length;
            for($i= $tmpMax; $i >$tmpMin; $i--){
                $keyArr[] = $this->queueName . self::VALU_KEY . $i;
            }
        }

        $result = @memcache_get(self::$client, $keyArr);

        return $result;
    }

    /**
     * 取出队列数据
     * @param int $length 要取出的长度(反向读取使用负数)
     * @return array
     */
    public function get($length = 0) {
        if (!is_numeric($length)) {
            return false;
        }
        if(!$this->_getLock()) {
            return false;
        }

        if ($this->_isEmpty()) {
            $this->_unLock();
            return false;
        }

        if (empty($length)) {
            $length = self::MAXNUM;
        }//默认所有
        $length = intval($length);
        $keyArr = array();
        if ($length >0) {//正向读取(从队列首向队列尾)
            $tmpMin = $this->currentHead;
            $tmpMax = $tmpMin + $length;
            for ($i = $tmpMin; $i <= $tmpMax; $i ++) {
                $keyArr[] = $this->queueName . self::VALU_KEY . $i;
            }
            $this->_changeHead($length);
        } else {//反向读取(从队列尾向队列首)
            $tmpMax = $this->currentTail;
            $tmpMin = $tmpMax + $length;
            for ($i = $tmpMax; $i > $tmpMin; $i --) {
                $keyArr[] = $this->queueName . self::VALU_KEY . $i;
            }
            $this->_changeTail(abs($length), true);
        }
        $result = @memcache_get(self::$client, $keyArr);

        foreach ($keyArr as $v) {//取出之后删除
            @memcache_delete(self::$client, $v, 0);
        }

        $this->_unLock();

        return $result;
    }

    /**
     * 清空队列
     */
    public function clear(){
        if(!$this->_getLock()) {
            return false;
        }

        if($this->_isEmpty()){
            $this->_unLock();
            return false;
        }

        $tmpMin = $this->currentHead--;
        $tmpMax = $this->currentTail++;

        for ($i = $tmpMin; $i <= $tmpMax; $i ++){
            $tmpKey = $this->queueName . self::VALU_KEY . $i;
            @memcache_delete(self::$client, $tmpKey, 0);
        }

        $this->currentTail = $this->currentHead = 0;
        memcache_set(self::$client, $this->head_key,  $this->currentHead, false, $this->expire);
        memcache_set(self::$client, $this->tail_key, $this->currentTail, false, $this->expire);

        $this->_unLock();
    }

    /*
     * 清除所有memcache缓存数据
     */
    public function memFlush() {
        memcache_flush(self::$client);
    }
}