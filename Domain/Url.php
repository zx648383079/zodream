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
		$urlhash = md5($key . $url);
		$len     = strlen($urlhash);
	
		//将加密后的串分成4段，每段4字节，对每段进行计算，一共可以生成四组短连接
		for ($i = 0; $i < 4; $i ++) {
			$urlhash_piece = substr($urlhash, $i * $len / 4, $len / 4);
			//将分段的位与0x3fffffff做位与，0x3fffffff表示二进制数的30个1，即30位以后的加密串都归零
			$hex = hexdec($urlhash_piece) & 0x3fffffff; //此处需要用到hexdec()将16进制字符串转为10进制数值型，否则运算会不正常
	
			$short_url = "http://t.cn/";
			//生成6位短连接
			for ($j = 0; $j < 6; $j++) {
				//将得到的值与0x0000003d,3d为61，即charset的坐标最大值
				$short_url .= self::$charset[$hex & 0x0000003d];
				//循环完以后将hex右移5位
				$hex = $hex >> 5;
			}
			$short_url_list[] = $short_url;
		}
		return $short_url_list;
	}
	
	
	/**
	 * 将一个URL转换为完整URL
	 * PHP将相对路径URL转换为绝对路径URL
	 */
	function format_url($srcurl, $baseurl) {
		$srcinfo = parse_url($srcurl);
		if(isset($srcinfo['scheme'])) {
			return $srcurl;
		}
		$baseinfo = parse_url($baseurl);
		$url = $baseinfo['scheme'].'://'.$baseinfo['host'];
		if(substr($srcinfo['path'], 0, 1) == '/') {
			$path = $srcinfo['path'];
		}else{
			$filename=  basename($baseinfo['path']);
			//兼容基础url是列表
			if(strpos($filename,".")===false){
				$path = dirname($baseinfo['path']).'/'.$filename.'/'.$srcinfo['path'];
			}else{
				$path = dirname($baseinfo['path']).'/'.$srcinfo['path'];
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