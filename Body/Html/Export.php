<?php
namespace Zodream\Body\Html;
/*
 * 导出类
 *
 * @author Jason
 * @time 2015-12-1
 */
class Export {
	public static function csv($text, $file) {
		header("Content-type:text/csv;");
		header("Content-Disposition:attachment;filename=" . $file.".csv");
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
		header('Expires:0');
		header('Pragma:public');
		echo $text;
	}
}