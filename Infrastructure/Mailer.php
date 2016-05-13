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
		$config = Config::getInstance()->get('mail');
		$email = empty($config['email']) ? $config['user'] : $config['email'];
		$name = empty($config['name']) ? $email : $config['name'];
		$this->setHost($config['host'], $config['port'])
			->setUser($config['user'], $config['password'])
			->setFrom($email, $name);
	}

	/**
	 * 设置发送者的信息
	 * @param string $username
	 * @param string $password
	 * @return $this
	 */
	public function setUser($username, $password) {
		$this->_mail->Username = $username;
		$this->_mail->Password = $password;
		return $this;
	}

	/**
	 * 设置host
	 * @param string $host
	 * @param string $port
	 * @return $this
	 */
	public function setHost($host, $port) {
		$this->_mail->Host = $host;
		$this->_mail->Port = $port;
		return $this;
	}

	/**
	 * 设置发件人
	 * @param string $address
	 * @param string $name
	 * @param bool|string $auto
	 * @return $this
	 */
	public function setFrom($address, $name = '', $auto = TRUE) {
		$this->_mail->setFrom($address, $name, $auto);
		return $this;
	}

	/**
	 * 添加接收者
	 * @param string $address
	 * @param string $name
	 * @return $this
	 */
	public function addAddress($address, $name = '') {
		$this->_mail->addAddress($address, $name);
		return $this;
	}

	/**
	 * 添加转发
	 * @param string $address
	 * @param string $name
	 * @return $this
	 */
	public function addReplyTo($address, $name = '') {
		$this->_mail->addReplyTo($address, $name);
		return $this;
	}

	/**
	 * 添加抄送
	 * @param string $address
	 * @param string $name
	 * @return $this
	 */
	public function addCC($address, $name = '') {
		$this->_mail->addCC($address, $name);
		return $this;
	}

	/**
	 * 添加
	 * @param string $address
	 * @param string $name
	 * @return $this
	 */
	public function addBCC($address, $name = '') {
		$this->_mail->addBCC($address, $name);
		return $this;
	}

	/**
	 * 添加附件
	 * @param string $file
	 * @param string $name
	 * @return $this
	 */
	public function addAttachment($file, $name = '') {
		$this->_mail->addAttachment($file, $name);
		return $this;
	}

	/**
	 * 发送的内容是否是html 或 plain
	 * @param bool $isHtml
	 * @return $this
	 */
	public function isHtml($isHtml = TRUE) {
		$this->_mail->isHtml($isHtml);
		return $this;
	}

	/**
	 * 发送
	 * @param string $subject
	 * @param string $body
	 * @param string $altBody
	 * @return bool
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