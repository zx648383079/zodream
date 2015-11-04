<?php 
namespace App\Lib\Html;

use App\Lib\Helper\HUrl;

class HPager implements IBase {
	private static $max = 0;
	
	public static function make($page) {
		$num = ceil( $page['total'] / $page['max']);
		
		if ($page['total'] <= 0 || $num <= 1) {
			return;
		}
		
		if ($page['index'] >= $page['total']) {
			$page['index'] = ($num - 1) * $page['max'];
		}
		
		self::$max = $page['max'];
		
		echo '<div class="pager">';
		
		$index = ceil( $page['index'] / $page['max'] ) + 1;
		
		
		if ( $page['index'] > 0) {
			echo '<a href="',self::url($index - 2),'">上一页</a>';
		}
		
		self::link( 1 , 1, $index );
		
		if ($num <= 5) {
			self::link( 2 , $num-1, $index );
		} else {
			if (1 < $index - 2) {
				echo '<span>...</span>';
			}
			
			self::link( 3 > $index ? 2 : ($index - 1 > $num - 2 ? $num - 2 : $index - 1) ,
			 			$index+1 > $num-1 ? $num-1 : ( $index + 1 < 3 ? 3 : $index + 1 ) ,
			 			$index );
			
			if ( $num > $index + 2 ) {
				echo '<span>...</span>';
			}
		}
		
		self::link( $num , $num, $index );		
		
		if ($index < $num) {
			echo '<a href="', self::url($index), '">下一页</a>';
		}
		
		echo '</div>';
	}
	
	private static function link( $i , $len, $index) {
		for (;$i <= $len; $i ++) { 
			if ($index == $i) {
				echo '<span>',$i,'</span>';
			} else {
				echo '<a href="', self::url($i - 1), '">', $i, '</a>';
			}
		}
	}
	
	private static function url($page) {
		return HUrl::to(null, array (
			'page' => $page * self::$max
			)
		);
	}
}