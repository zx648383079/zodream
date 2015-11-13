<?php 
namespace App\Model;

class TempletModel extends Model {
	protected $table = "templet";
	
	protected $fillable = array (
		'title',
		'udate',
		'cdate'
	);
	
	/**
	 * 
	 * @param number $limit
	 * @param number $amount
	 * @return multitype:
	 */
	public function findLimit($limit = 0, $amount = 20) {
		return $this->find(array(
			'limit' => $limit.','.$amount
		));
	}
	
	public function updateById($data, $id) {
		return parent::updateById($data, $id);
	}
	
	public function deleteById($id) {
		return $this->delete('id = '.$id);
	}
	
	/**
	 * 查找一条数据
	 * @see \App\Lib\Db\DbFactory::findById()
	 */
	public function findById($id, $filed = null) {
		return parent::findById($id, $filed);
	}
}