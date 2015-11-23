<?php
namespace App\Lib\Socket;

class SServer {
	private $_port               = '9000';
	private $_host               = '127.0.0.1';
	private $_client_socket_list = array();
	
	public function __construct($host, $port) {
		$this->_host = $host;
		$this->_port = $port;
	}
	
	private function _showError($error) {
		exit($error);
	}
	/**
	 * 开始进行socket服务器端监听端口
	 */
	public function start() {
		// 创建端口
		if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
			$this->_showError('socket_create() failed :reason:' . socket_strerror(socket_last_error()));
		}
		// 绑定
		if (socket_bind($sock, $this->_host, $this->_port ) === false) {
			$this->_showError('socket_bind() failed :reason:' . socket_strerror ( socket_last_error($sock)));
		}
		// 监听
		if (socket_listen($sock, 5) === false) {
			$this->_showError('socket_bind() failed :reason:' . socket_strerror ( socket_last_error ( $sock ) ) );
		}
		do {
			//当有一个客户端连接的时候
			if ($client_socket = socket_accept ( $sock )) {
				$count                       = count($this->_client_socket_list) + 1;
				//把新来的用户加入 客户端数组里
				$this->_client_socket_list[] = $client_socket;
				echo "new connection:\r\n";//服务器端输出当前正在连接的客户端数量
				echo "current connection:{$count}\r\n";
				//接受客户端传过来的字符串
				$msg = $this->read($client_socket);
				echo "client:{$msg}\r\n";
				//服务器向客户端传值
				$my_msg = "I am fine,think you\r\n";
				$this->send($client_socket, $my_msg);
			}
			/**
			 * 这段代码给你参考,用来判断是否有客户端主动失去连接
			 else{
			 foreach ( $this->_client_socket_list as $socket ) {
			 $len = socket_recv ($socket, $buffer, 2048, 0 ); // 接受一下客户端信息,如果为0代表断开连接
			 if ($len < 7) {
			 //这里写是去连接的客户端业务
			 }
			 }
			 }
			 */
		} while (true);
	}
	/**
	 * 发送数据给客户端
	 */
	public function send($client_socket, $str) {
		return socket_write($client_socket, $str, strlen($str));
	}
	/**
	 * 从客户端接受数据
	 */
	public function read($client_socket){
		return socket_read($client_socket, 8192);//8192实际代表的接受长度,我用819292表示长一点,这样长一点的字符串也可以接受到,不到8192也没关系,会自动识别
	}
}