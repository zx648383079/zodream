<?php 
namespace App\Lib\Web;

interface IBase
{
	function get($name , $default);
	
	function post($name , $default);
}