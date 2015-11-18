<?php
namespace App\Lib\Enum;

class ESql extends EBase {
	const DATABASE  = 0;
	
	const TABLE     = 1;
	
	const COLUMN    = 2;
	
	const __default = self::DATABASE;
}