<?php 
namespace App\Head;
/*
* url 
* 
* @author Jason
* @time 2015-11.29
*/
class File_get_content {
	public static function get($url) {
		return file_get_contents($url);
	}
	
	public static function post($url, $args) {
		$context = array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-type: application/x-www-form-urlencoded'."".
				'User-Agent : Jimmy\'s POST Example beta'."".
				'Content-length: '.strlen($post_string)+8,
				'content' => 'mypost='.$post_string
			)
		);
		$stream_context = stream_context_create($context);
		$data           = file_get_contents($url, FALSE, $stream_context);
		return $data; 
	}
}