<?php 
/*********************************
用户权限分组表
*********************************/
namespace App\Model;

use App\Lib\Object\OTime;

class GroupModel extends Model{
	protected $table = "groups";
	
	protected $fillable = array(
		'id',
		'name',
		'roles'
	);
	
	public function addRoles($roles)
	{
		$model = $this->findOne(
			array(
				"roles = '{$roles}'"
			)
		);
		
		if(is_bool($model))
		{
			$id = $this->add(
				array(
					'roles' => $roles,
					'cdate' => OTime::Now()
				)
			);
		}else{
			$id = $model->id;
		}
		
		return $id;
	}
}