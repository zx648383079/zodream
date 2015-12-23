<?php
class Image{
	//水印配置项
	private $waterOn;
	private $waterImg;
	private $waterPos;
	private $waterPct;
	private $waterText;
	private $waterFont;
	private $waterTextSize;
	private $waterTextColor;
	private $qua;
	//缩略图配置项
	private $thumbWidth;
	private $thumbHeight;
	private $thumbType;
	private $thumbEndfix;
	//构造函数
	public function __construct(){
		/*
		 //水印处理
    "WATER_ON"=>1,//水印开关
    "WATER_IMG"=>"./data/logo.png",//水印图片
    "WATER_POS"=>9,//水印位置
    "WATER_PCT"=>80,//水印透明度
    "WATER_TEXT"=>"http://www.caoxiaobin.cn",
    "WATER_FONT"=>"./data/simsunb.ttf",//水印字体
    "WATER_TEXT_COLOR"=>"#333333",//文字颜色 16进制表示
    "WATER_TEXT_SIZE"=>16,//文字大小
    "WATER_QUA"=>80,//图片压缩比
    //缩略图
    "THUMB_WIDTH"=>150,//缩率图宽度
    "THUMB_HEIGHT"=>150,//缩略图高度
    "THUMB_TYPE"=>1,//缩略图处理  1宽度固定，高度自增 2高度固定，宽度自增 //缩略图尺寸不变，对原图进行裁切
    "THUMB_ENDFIX"=>"_thmub"//缩略图后缀
		 */
		$this->waterOn=C("WATER_ON");
		$this->waterImg=C("WATER_IMG");
		$this->waterPos=C("WATER_POS");
		$this->waterPct=C("WATER_PCT");
		$this->waterText=C("WATER_TEXT");
		$this->waterFont=C("WATER_FONT");
		$this->waterTextSize=C("WATER_TEXT_SIZE");
		$this->waterTextColor=C("WATER_TEXT_COLOR");
		$this->qua=C("WATER_QUA");
		//缩率图
		$this->thumbWidth=C("THUMB_WIDTH");
		$this->thumbHeight=C("THUMB_HEIGHT");
		$this->thumbType=C("THUMB_TYPE");
		$this->thumbEndFix=C("THUMB_ENDFIX");
	}
	/*
	 *验证图片是否合法
	 */
	private function check($img){
		return is_file($img)&&getimagesize($img)&&extension_loaded("gd");
	}
	/*
	 *缩率图
	 *@param string  $img         原图
	 *@param string  $outFile     缩率之后存储的图片
	 *@param int     $thumbWidth  缩率图宽度
	 *@param int     $thumbHeight 缩率图高度
	 *@param int     $thumbType   那种方式进行缩略处理
	 */
	public function thumb($img,$outFile="",$thumbWidth="",$thumbHeight="",$thumbType=""){
		if(!$this->check($img)){
			return false;
		}
		//缩率图处理方式
		$thumbType=$thumbType?$thumbType:$this->thumbType;
		//缩率图宽度
		$thumbWidth=$thumbWidth?$thumbWidth:$this->thumbWidth;
		//缩率图高度
		$thumbHeight=$thumbHeight?$thumbHeight:$this->thumbHeight;
		//获取原图信息
		$imgInfo=getimagesize($img);
		//原图宽度
		$imgWidth=$imgInfo[0];
		//原图高度
		$imgHeight=$imgInfo[1];
		//获得原图类型
		$imgtype=image_type_to_extension($imgInfo[2]);
		//根据不同的缩略处理方式，获得尺寸(原图和缩略图相应的尺寸)
		$thumb_size=$this->thumbsize($imgWidth,$imgHeight,$thumbWidth,$thumbHeight,$thumbType);
		//创建原图
		$func="imagecreatefrom".substr($imgtype,1);//变量函数
		$resImg=$func($img);
		//创建缩率图画布
		if($imgtype==".gif"){
			$res_thumb=imagecreate($thumb_size[2],$thumb_size[3]);
		}else{
			$res_thumb=imagecreatetruecolor($thumb_size[2],$thumb_size[3]);
		}
		imagecopyresized($res_thumb,$resImg,0,0,0,0,$thumb_size[2],$thumb_size[3],$thumb_size[0],$thumb_size[1]);
		$fileInfo=pathinfo($img);//文件信息
		$outFile=$outFile?$outFile:$fileInfo['filename'].$this->thumbEndFix.$fileInfo['extension'];//文件名称
		$outFile=$fileInfo["dirname"]."/".$outFile;//加上目录
		$func="image".substr($imgtype,1);
		$func($res_thumb,$outFile);
		return $outFile;
	}
	private function thumbSize($imgWidth,$imgHeight,$thumbWidth,$thumbHeight,$thumbType){
		//缩率图尺寸
		$w=$thumbWidth;
		$h=$thumbHeight;
		//原图尺寸
		$img_w=$imgWidth;
		$img_h=$imgHeight;
		switch($thumbType){
			case 1:
				//宽度固定，高度自增
				$h=$w/$imgWidth*$imgHeight;
				break;
			case 2://高度固定，宽度自
				$w=$h/$imgHeight*$imgWidth;
				break;
			case 3:
				if($imgHeight/$thumbHeight>$imgWidth/$thumbWidth){
					$img_h=$imgWidth/$thumbWidth*$thumbHeight;
				}else{
					$img_w=$imgHeight/$thumbHeight*$thumbWidth;
				}
		}
		return array($img_w,$img_h,$w,$h);
	}
	/*
	 *@param string  $img     原图
	 *@param string  $outImg  加完水印后生成的图
	 *@param int     $pos     水印位置
	 *@param int     $pct     透明度
	 *@param text    $text    水印文字
	 *@param string  $waterImg水印图片
	 */
	public function water($img,$outImg=null,$pos="",$pct="",$text="",$waterImg="",$textColor=""){
		if(!$this->check($img)){
			return false;
		}
		//加完水印后生成的图
		$outImg=$outImg?$outImg:$img;
		//水印位置
		$pos=$pos?$pos:$this->waterPos;
		//透明度
		$pct=$pct?$pct:$this->waterPct;
		//水印文字
		$text=$text?$text:$this->waterText;
		//水印图片
		$waterImg=$waterImg?$waterImg:$this->waterImg;
		//验证水印图片
		$waterImgOn=$this->check($waterImg);
		//水印文字颜色
		$textColor=$textColor?$textColor:$this->waterTextColor;
		//原图信息
		$imgInfo=getimagesize($img);
		//原图宽度
		$imgWidth=$imgInfo[0];
		//原图高度
		$imgHeight=$imgInfo[1];
		switch($imgInfo[2]){
			case 1:
				$resImg=imagecreatefromgif($img);
				break;
			case 2:
				$resImg=imagecreatefromjpeg($img);
				break;
			case 3:
				$resImg=imagecreatefrompng($img);
				break;
		}
		if($waterImgOn){//水印图片有效
			//水印信息
			$waterInfo=getimagesize($waterImg);
			//水印宽度
			$waterWidth=$waterInfo[0];
			//水印高度
			$waterHeight=$waterInfo[1];
			//根据不同的情况创建不同的类型 gif jpeg png
			$w_img=null;
			switch($waterInfo[2]){
				case 1:
					$w_img=imagecreatefromgif($waterImg);
					break;
				case 2:
					$w_img=imagecreatefromjpeg($waterImg);
					break;
				case 3:
					$w_img=imagecreatefrompng($waterImg);
			}
		}else{//水印图片失效，使用文字水印
			if(empty($text)||strlen($textColor)!==7){
				return false;
			}
			//获得文字水印盒子信息
			$textInfo=imagettfbbox($this->waterTextSize,0,$this->waterFont,$text);
			//文字信息宽度
			$textWidth=$textInfo[2]-$textInfo[6];
			//文字信息高度
			$textHeight=$textInfo[3]-$textInfo[7];
		}
		//水印位置
		$x=$y=20;
		switch($pos){
			case 1:
				break;
			case 2:
				$x=($imgWidth-$waterWidth)/2;
				break;
			case 3:
				$y=$imgWidth-$waterWidth-10;
				break;
			case 4:
				$x=($imgHeight-$waterHeight)/2;
				break;
			case 5:
				$x=($imgWidth-$waterWidth)/2;
				$y=($imgHeight-$waterHeight)/2;
				break;
			case 6:
				$x=$imgWidth-$waterWidth-10;
				$y=($imgHeight-$waterHeight)/2;
				break;
			case 7:
				$x=$imgHeight-$waterHeight-10;
				break;
			case 8:
				$x=($imgWidth-$waterWidth)/2;
				$y=$imgHeight-$waterHeight-10;
				break;
			case 9:
				$x=$imgWidth-$waterWidth-10;
				$y=$imgHeight-$waterHeight-10;
				break;
			default:
				$x=mt_rand(20,$imgWidth-$waterWidth);
				$y=mt_rand(20,$imgHeight-$waterHeight);
		}
		if($waterImgOn){//当水印图片有效时，以图片形式加水印
			if($waterInfo[2]==3){
				imagecopy($resImg,$w_img,$x,$y,0,0,$waterWidth,$waterHeight);
			}else{
				imagecopymerge($resImg,$w_img,$x,$y,0,0,$waterInfo,$waterHeight,$pct);
			}
		}else{//水印图片失效，以文字水印加
			$red=hexdec(substr($this->waterTextColor,1,2));
			$greem=hexdec(substr($this->waterTextColor,3,2));
			$blue=hexdec(substr($this->waterTextColor,5,2));
			$color=imagecolorallocate($resImg,$red,$greem,$blue);
			imagettftext($resImg,$this->waterTextSize,0,$x,$y,$color,$this->waterFont,$text);
		}
		//输出图片
		switch($imgInfo[2]){
			case 1:
				imagegif($resImg,$outImg);
				break;
			case 2:
				imagejpeg($resImg,$outImg);
				break;
			case 3:
				imagepng($resImg,$outImg);
				break;
		}
		//垃圾回收
		if(isset($resImg)){
			imagedestroy($resImg);
		}
		if(isset($w_img)){
			imagedestroy($w_img);
		}
		return true;
	}
}