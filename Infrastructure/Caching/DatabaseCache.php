<?php 
namespace Zodream\Infrastructure\Caching;

/**
* 数据库缓存类
* 
* @author Jason
*/
use Zodream\Infrastructure\Database\Command;
use Zodream\Infrastructure\Database\Query;

class DatabaseCache extends Cache {
	/**
	 *  ```php
	 * CREATE TABLE cache (
	 *     id char(128) NOT NULL PRIMARY KEY,
	 *     expire int(11),
	 *     data BLOB
	 * );
	 * ```
	 * @var Command
	 */
	protected $db;
	
	protected $table = 'cache';



	public function __construct() {
		$this->db = Command::getInstance();
		$this->db->setTable($this->table);
	}

	protected function query() {
		$query = new Query();
		$query->from($this->table);
		return $query;
	}
	
	protected function getValue($key) {
		return $this->query()->select('data')->where(array(
			'id' => $key,
			'(expire = 0 OR expire > '.time().')',
		))->scalar();
	}
	
	protected function setValue($key, $value, $duration) {
		 $result = $this->db->update('expire = :expire, data = :data', 'id = :id', array(
			':expire' => $duration > 0 ? $duration + time() : 0,
			':data' => [$value, \PDO::PARAM_LOB],
			':id' => $key
		));
		if (empty($result)) {
			$this->gc();
			return true;
		}
		return $this->addValue($key, $value, $duration);
	}
	
	protected function addValue($key, $value, $duration) {
		$this->gc();
		try {
			$this->db->insert('id, expire, data', ':id, :expire, :data', array(
					':id' => $key,
					':expire' => $duration > 0 ? $duration + time() : 0,
					':data' => [$value, \PDO::PARAM_LOB],
				));
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	public function gc($force = false) {
		if ($force || mt_rand(0, 1000000) < $this->gcChance) {
			$this->db->delete('expire > 0 AND expire < ' . time());
		}
	}
	
	protected function hasValue($key) {
		$count = $this->query()->count()->where(array(
			'id' => $key,
			'(expire = 0 OR expire > '.time().')',
		))->scalar();
		return !empty($count);
	}
	
	protected function deleteValue($key) {
		$this->db->delete('id = :id', array(
			':id' => $key
		));
	}
	
	protected function clearValue() {
		$this->db->delete();
	}
}