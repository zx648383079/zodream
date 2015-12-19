<?php 
namespace Zodream\Infrastructure;
/**
* mail
* 
* @author Jason
* @time 2015-11-29
*/
class Mailer {

	private $_mail;
	
	public function __construct() {
		$this->_mail          = new \PHPMailer;
		$this->_mail->CharSet = 'UTF-8';
		$this->_mail->isSMTP();
		$this->_mail->SMTPAuth = true;
		$this->_mail->SMTPSecure = 'tls';
		if (defined('DEBUG') && DEBUG) {
			$this->_mail->SMTPDebug = 1;
		}
	
		$config = App::config('mail');
		$this->setHost($config['host'], $config['port'])->setUser($config['user'], $config['password']);
	}
	
	/**
	 * 设置发送者的信息
	 * @param unknown $username
	 * @param unknown $password
	 */
	public function setUser($username, $password) {
		$this->_mail->Username = $username;
		$this->_mail->Password = $password;
		return $this;
	}
	
	/**
	 * 设置host
	 * @param unknown $host
	 * @param unknown $port
	 */
	public function setHost($host, $port) {
		$this->_mail->Host = $host;
		$this->_mail->Port = $port;
		return $this;
	}
	
	/**
	 * 设置接收者
	 * @param unknown $address
	 * @param string $name
	 * @param string $auto
	 */
	public function setFrom($address, $name = '', $auto = TRUE) {
		$this->_mail->setFrom($address, $name, $auto);
		return $this;
	}
	
	/**
	 * 添加接收者
	 * @param unknown $address
	 * @param string $name
	 */
	public function addAddress($address, $name = '') {
		$this->_mail->addAddress($address, $name);
		return $this;
	}
	
	/**
	 * 添加转发
	 * @param unknown $address
	 * @param string $name
	 */
	public function addReplyTo($address, $name = '') {
		$this->_mail->addReplyTo($address, $name);
		return $this;
	}
	
	/**
	 * 添加抄送
	 * @param unknown $address
	 * @param string $name
	 */
	public function addCC($address, $name = '') {
		$this->_mail->addCC($address, $name);
		return $this;
	}
	
	/**
	 * 添加
	 * @param unknown $address
	 * @param string $name
	 */
	public function addBCC($address, $name = '') {
		$this->_mail->addBCC($address, $name);
		return $this;
	}
	
	/**
	 * 添加附件
	 * @param unknown $file
	 * @param string $name
	 */
	public function addAttachment($file, $name = '') {
		$this->_mail->addAttachment($file, $name);
		return $this;
	}
	
	/**
	 * 发送的内容是否是html 或 plain
	 * @param string $isHtml
	 */
	public function isHtml($isHtml = TRUE) {
		$this->_mail->isHtml($isHtml);
		return $this;
	}
	
	/**
	 * 发送
	 * @param unknown $subject
	 * @param unknown $body
	 * @param string $altBody
	 */
	public function send($subject, $body, $altBody = '') {
		$this->_mail->Subject = $subject;
		$this->_mail->Body    = $body;
		$this->_mail->AltBody = $altBody;
		return $this->_mail->send();
	}
	
	/**
	 * 获取错误信息
	 */
	public function getError() {
		return $this->_mail->ErrorInfo;
	}
	
	public function __set($name, $value) {
		if (isset($this->_mail->$name)) {
			$this->_mail->$name = $value;
		}
	}
}