<?php 
namespace Zodream\Domain;
/**
 * url
 *
 * @author Jason
 * @time 2015-12-1
 */



class Url {
	
	#字符表
	public static $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	
	/**
	 * 生成短链接
	 *
	 * @param $url
	 * @return array
	 */
	public static function short($url) {
		$key     = "alexis";
		$urlHash = md5($key . $url);
		$len     = strlen($urlHash);
		$shortUrlList = array();
		//将加密后的串分成4段，每段4字节，对每段进行计算，一共可以生成四组短连接
		for ($i = 0; $i < 4; $i ++) {
			$urlHashPiece = substr($urlHash, $i * $len / 4, $len / 4);
			//将分段的位与0x3fffffff做位与，0x3fffffff表示二进制数的30个1，即30位以后的加密串都归零
			$hex = hexdec($urlHashPiece) & 0x3fffffff; //此处需要用到hexdec()将16进制字符串转为10进制数值型，否则运算会不正常
	
			$shortUrl = "http://t.cn/";
			//生成6位短连接
			for ($j = 0; $j < 6; $j++) {
				//将得到的值与0x0000003d,3d为61，即charset的坐标最大值
				$shortUrl .= self::$charset[$hex & 0x0000003d];
				//循环完以后将hex右移5位
				$hex = $hex >> 5;
			}
			$shortUrlList[] = $shortUrl;
		}
		return $shortUrlList;
	}


	/**
	 * 将一个URL转换为完整URL
	 * PHP将相对路径URL转换为绝对路径URL
	 * @param string $srcUrl
	 * @param string $baseUrl
	 * @return string
	 */
	function formatUrl($srcUrl, $baseUrl) {
		$srcInfo = parse_url($srcUrl);
		if(isset($srcInfo['scheme'])) {
			return $srcUrl;
		}
		$baseInfo = parse_url($baseUrl);
		$url = $baseInfo['scheme'].'://'.$baseInfo['host'];
		if(substr($srcInfo['path'], 0, 1) == '/') {
			$path = $srcInfo['path'];
		}else{
			$filename=  basename($baseInfo['path']);
			//兼容基础url是列表
			if(strpos($filename,".")===false){
				$path = dirname($baseInfo['path']).'/'.$filename.'/'.$srcInfo['path'];
			}else{
				$path = dirname($baseInfo['path']).'/'.$srcInfo['path'];
			}
			 
		}
		$rst = array();
		$path_array = explode('/', $path);
		if(!$path_array[0]) {
			$rst[] = '';
		}
		foreach ($path_array AS $key => $dir) {
			if ($dir == '..') {
				if (end($rst) == '..') {
					$rst[] = '..';
				}elseif(!array_pop($rst)) {
					$rst[] = '..';
				}
			}elseif($dir && $dir != '.') {
				$rst[] = $dir;
			}
		}
		if(!end($path_array)) {
			$rst[] = '';
		}
		$url .= implode('/', $rst);
		return str_replace('\\', '/', $url);
	}
}