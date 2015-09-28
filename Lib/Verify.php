<?php
namespace App\Lib;

use App;

class Verify
{
	 const CHARSET = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';//随机因子
	 public $code;         	//验证码
	 private $codelen = 4;  	//验证码长度
	 private $width = 130; 		//宽度
	 private $height = 50; 		//高度
	 private $img;				//图形资源句柄
	 private $font;				//指定的字体
	 private $fontsize=30 ;			//指定字体大小
	 private $fontcolor;		//指定字体颜色
	 
	 /**
	 * 公有构造
	 *
	 * @access public
	 *
	 *
	 */
	 public function load()
	{
		$configs = array_merge( App::config('verify'), func_get_args() );
	
		$this->codelen = isset($configs['length'])?$configs['length']:4;
		$this->width = isset($configs['width'])?$configs['width'] : 130 ;
		$this->height = isset($configs['height'])?$configs['height'] : 50 ;
		$this->fontsize = isset($configs['fontsize'])?$configs['fontsize'] : 30 ;
		$this->font = isset($configs['font'])?$configs['font'] : 'asset/font/AcademyKiller.ttf';
		
	}
	

	/**
	 *生成随机码
     */
	private function createCode() {
		 $charset = self::CHARSET;
		  $_len = strlen($charset)-1;
		  for ($i=0;$i<$this->codelen;$i++) {
		   	$this->code .= $charset[mt_rand(0,$_len)];
		  }
	 }
	 //生成背景
	 private function createBg() {
		  $this->img = imagecreatetruecolor($this->width, $this->height);
		  $color = imagecolorallocate($this->img, mt_rand(157,255), mt_rand(157,255), mt_rand(157,255));
		  imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
	 }
	 //生成文字
	 private function createFont() {
		  $_x = $this->width / $this->codelen;
		  for ($i=0;$i<$this->codelen;$i++) {
			   $this->fontcolor = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
			   imagettftext($this->img,$this->fontsize,mt_rand(-30,30),$_x*$i+mt_rand(1,5),$this->height / 1.4,$this->fontcolor,$this->font,$this->code[$i]);
		  }
	 }
	 //生成线条、雪花
	 private function createLine() {
		  //线条
		  for ($i=0;$i<6;$i++) {
			   $color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
			   imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
		  }
		  //雪花
		  for ($i=0;$i<100;$i++) {
			   $color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
			   imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
		  }
	 }
	//对外生成
	 public function render() {
		 if(empty($this->font))
		 {
			$this->load();	 
		 }
		 
		  $this->createBg();
		  $this->createCode();
		  $this->createLine();
		  $this->createFont();
		  
		  return $this->img;
	 }
}