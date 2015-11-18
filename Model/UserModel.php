<?php 
namespace App\Model;
/*********************************
用户表的连接
create table zx_users ( 
	id int(11) not null AUTO_INCREMENT PRIMARY KEY, 
	email varchar(100) not null UNIQUE,
	name varchar(20) not null UNIQUE,
	pwd varchar(32),
	token varchar(32),
	udate int(11),
	cdate int(11) 
)charset = utf8;
*********************************/
use App\Lib\Object\OTime;

class UserModel extends Model {
	protected $table = "users";
	
	
	protected $fillable = array (
		'email',
		'name',
		'pwd',
		'token',
		'udate',
		'cdate'
	);
	/******
	从网页注册
	*/
	public function fillWeb($data) {
		$data['pwd']   = md5($data['pwd']);
		$data['udate'] = $data['cdate'] = OTime::Now();
		return $this->add($data);
	}
	
	public function findByEmail($email) {
		return $this->findOne(array("email = '{$email}'"));
	}
	
	public function findByToken($token) {
		$result = $this->findOne(array("token = '{$token}'"));
		return $result['id'];
	}
	
	public function setToken($id) {
		$token =  md5(microtime().$id);
		$this  -> update(array('token' => $token), array("id = {$id}"));
		return $token;
	}
	
	public function clearToken($id) {
		$this->update(array('token' => 'null'), array("id = {$id}"));
	}
	
	public function findByUser($data) {
		$pwd = md5($data['pwd']);
		$result = $this->findOne(array("email = '{$data['email']}'", "pwd = '{$pwd}'"));
		return $result['id'];
	}
	
	public function findWithRoles($where, $field) {
		$sql = array (
			'select' => $field,
			'from'   => "{$this->table} u",
			'left'   => array (
				'groups g',
				'u.group = g.id'
			),
			'where'  => $where
		);
		return $this->findByHelper($sql);
	}
	
	public function role() {
		return $this->hasOne('App\Model\GroupModel', 'group', 'id');
	}
}