<?php
namespace Zodream\Domain\Image;
/**
 * 验证码
 *
 * @author Jason
 */
use Zodream\Infrastructure\Config;

final class VerifyCode extends WaterMark {
	const CHARSET     = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';//随机因子
	private $_code;         	//验证码
	private $_length  = 4;  	    //验证码长度
	private $_fontFamily;				//指定的字体
	private $_fontsize = 30 ;	//指定字体大小
	private $_fontcolor;		    //指定字体颜色
	
	public function getCode() {
		return $this->_code;
	}
	
	public function generate() {
		$this->_loadConfig();
		$this->_createCode();
		$this->_createBg();
		$this->_createText();
		$this->_createLine();
		return $this->image;
	}
	
	/**
	 * 载入配置
	 */
	private function _loadConfig() {
		$configs        = array_merge(Config::getInstance()->get('verify', array()));
		$this->_length  = isset($configs['length']) ? $configs['length'] : 4;
		$this->width   = isset($configs['width']) ? $configs['width'] : 50 ;
		$this->height   = isset($configs['height']) ? $configs['height'] : 25 ;
		$this->_fontsize = isset($configs['fontsize']) ? $configs['fontsize'] : 30 ;
		$this->_fontFamily     = isset($configs['font']) ? $configs['font'] : 5;
	
	}
	
	
	/**
	 * 生成随机码
	 */
	private function _createCode() {
		$charset = self::CHARSET;
		$_len   = strlen($charset)-1;
		for ($i = 0; $i < $this->_length; $i ++) {
			$this->_code .= $charset[mt_rand(0, $_len)];
		}
	}
	
	/**
	 * 生成背景
	 */
	private function _createBg() {
		$this->create($this->width, $this->height);
		imagefilledrectangle(
				$this->image, 
				0, 
				$this->height, 
				$this->width, 
				0, 
				$this->getColorWithRGB(mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255))
		);
	}
	
	/**
	 * 生成文字
	 */
	private function _createText() {
		$x = $this->width / $this->_length;
		for ($i = 0 ; $i < $this->_length; $i ++) {
			$this->addText(
					$this->_code[i],
					$x*$i + mt_rand(1, 5),
					$this->height / 1.4,
					$this->_fontsize,
					$this->getColorWithRGB(mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156)),
					$this->_fontFamily,
					mt_rand(-30, 30)
			);
		}
	}
	
	/**
	 * 生成线条、雪花
	 */
	private function _createLine() {
		//线条
		for ($i = 0; $i < 6; $i ++) {
			imageline(
					$this->image, 
					mt_rand(0, $this->width), 
					mt_rand(0, $this->height), 
					mt_rand(0, $this->width), 
					mt_rand(0, $this->height), 
					$this->getColorWithRGB(mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156))
			);
		}
		//雪花
		for ($i = 0; $i < 100; $i ++) {
			imagestring(
					$this->image, 
					mt_rand(1, 5), 
					mt_rand(0, $this->width), 
					mt_rand(0, $this->height), 
					'*', 
					$this->getColorWithRGB(mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255))
			);
		}
	}
}