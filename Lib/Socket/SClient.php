<?php
namespace App\Lib\Socket;

class SClient {
	private $_host; //连接socket的主机
    private $_port; //socket的端口号 
    private $_error    = array();
    private $_socket   = null; //socket的连接标识
    private $_queryStr = ''; //发送的数据
    
    public function __construct($host, $port) {
        if (!extension_loaded('sockets')) {
            exit('请打开socket扩展 ');
        }
        $this->_host = $host;
        $this->_port = $port;
        $this->_create();    
    }

    /**
     * 创建socket
     */
    private function _create(){
    	if (empty($this->_socket)) {
    		$this->_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);//创建socket
    	}
        $link = @socket_connect($this->_socket, $this->_host, $this->_port);
        if ($link){
            return $link;
        }else{
            $this->_error[] = socket_last_error($this->_socket);
            return false;
        }
    }

    public function send($data) {
    	$this->_write($data);
    	return $this->_read();
    }
    
    /**
     * 向socket服务器写入数据
     * @param string $contents
     */
    private function _write($contents) {
        $this->_queryStr = $contents;
        if (empty($this->_socket)) {
        	$this->_create();
        }
        $contents = $this->_fliterSendData($contents);
        $result   = socket_write($this->_socket, $contents, strlen($contents));
        if (!intval($result)) {
            $this->_error[] = socket_last_error($this->_socket);
            return false;
        }
    }
    
    /**
     * 读取响应数据
     * @return boolean|string
     */
    private function _read() {
    	$response = socket_read($this->_socket, 12048);
    	if (false === $response) {
    		$this->_error[] = socket_last_error($this->_socket);
    		return false;
    	}
    	return $response;
    }

    /**
     * 对发送的数据进行过滤
     * @param unknown $contents
     * @return unknown
     */
    private function _fliterSendData($contents) {
        //对写入的数据进行处理
        return $contents;
    }

    /**
     * 所有错误信息
     */ 
    public function getError() {
        return $this->_error;
    }

    /**
     * 最后一次错误信息
     */
    public function getLastError() {
        return end($this->_error);
    }
    
    /**
     * 获取最后一次发送的消息
     * @return Ambigous <string, string>
     */
    public function getLastMsg() {
        return $this->_queryStr;
    }

    public function getHost() {
        return $this->_host;
    }

    public function getPort() {
        return $this->_port;
    }

    /**
     * 关闭socket连接
     */
    private function _close() {
    	if (empty($this->_socket)) {
    		socket_close($this->socket);//关闭连接
    		$this->_socket = null; //连接资源初始化
    	}
    }

    public function __destruct() {
        $this->_close();
    }
}