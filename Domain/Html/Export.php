<?php
namespace Zodream\Body\Html;
/**
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
	
	
	public static $readerObj;
	public static $charset = 'utf-8';
	
	/**
	 * 输出切换编码
	 */
	public static function excelExportIconv($output){
	
		return iconv(self::$charset, 'GBK', $output);
	}
	
	/**
	 * 导出文件
	 * @param $fileName  string
	 * @param $title     string
	 * @param $firstRow  array
	 *          如：array('name'=>'名字', 'title' => '标题') 键名与后面的数组$data的子元素键名关联
	 * @param $data      array
	 */
	public static function exportFile($fileName, $title = '', $firstRow = array(), $data = array())
	{
		header('Content-Type: application/vnd.ms-execl');
		header('Content-Disposition: attachment; filename=' . $fileName . '.xls');
		header('Pragma: no-cache');
		header('Expires: 0');
	
		if (!empty($title)) {
			echo self::excelExportIconv($title) . "\t\n";
		}
	
		/**
		 *  第一行与后面的数据以键名关联
		 */
		if (!empty($firstRow) && is_array($firstRow)) {
	
			//输出第一行内容
			foreach ($firstRow as $first) {
				echo self::excelExportIconv($first) . "\t";
			}
			echo "\n";
	
			if (!empty($data) && is_array($data)) {
				foreach ($data as $item) {
					foreach ($firstRow as $_key => $_val) {
						if (isset($item[$_key])) {
							echo self::excelExportIconv($item[$_key]) . "\t";
						} else {
							echo self::excelExportIconv('') . "\t";
						}
					}
					echo "\n";
				}
			}
		} else {
	
			if (!empty($data) && is_array($data)) {
				foreach ($data as $item) {
					foreach ($item as $val) {
						echo self::excelExportIconv($val) . "\t";
					}
					echo "\n";
				}
				echo "\n";
			}
		}
	
	}
}