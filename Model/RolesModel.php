<?php 
/*********************************
用户权限表
*********************************/
namespace App\Model;


class RolesModel extends Model
{
	protected $table = "roles";
	
	protected $fillable = array(
		'id',
		'name'
	);
}