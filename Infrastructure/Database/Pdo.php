<?php 
namespace Zodream\Infrastructure\Database;
/**
* pdo
* 
* @author Jason
*/
use Zodream\Infrastructure\Error;
use Zodream\Infrastructure\EventManager\EventManger;

class Pdo extends Database {

	/**
	 * @var \PDO
	 */
	protected $driver = null;

	/**
	 * @var \PDOStatement
	 */
	protected $result;

	protected function connect() {
		try {
			//$this->driver = new \PDO('mysql:host='.$host.';port='.$port.';dbname='.$database, $user, $pwd ,
			//                     array(\PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES {$coding}"));
			$this->driver = new \PDO (
					'mysql:host='. $this->configs['host'].
					';port='.$this->configs['port'].
					';dbname='.$this->configs['database'], 
					$this->configs['user'], 
					$this->configs['password']
			);
			$this->driver->exec ('SET NAMES '.$this->configs['encoding']);
			$this->driver->query ( "SET character_set_client={$this->configs['encoding']}" );
			$this->driver->query ( "SET character_set_connection={$this->configs['encoding']}" );
			$this->driver->query ( "SET character_set_results={$this->configs['encoding']}" );
			$this->driver->setAttribute (\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch (\PDOException $ex) {
			Error::out($ex->getMessage(), __FILE__, __LINE__);
		}
	}
	
	
	/**
	 * 获取最后修改的id
	 * @return string
	 */
	public function lastInsertId() {
		return $this->driver->lastInsertId();
	}
	
	public function rowCount() {
		return $this->result->rowCount();
	}
	
	/**
	 * 预处理
	 * @param string $sql
	 */
	public function prepare($sql) {
		$this->result = $this->driver->prepare($sql);
	}
	
	/**
	 * 绑定值
	 * @param array $param
	 */
	public function bind(array $param) {
		foreach ($param as $key => $value) {
			if (is_null($value)) {
				$type = \PDO::PARAM_NULL;
			} else if (is_bool($value)) {
				$type = \PDO::PARAM_BOOL;
			} else if (is_int($value)) {
				$type = \PDO::PARAM_INT;
			} else {
				$type = \PDO::PARAM_STR;
			}
			$this->result->bindValue(is_int($key) ? ++$key : $key, $value, $type);
		}
	}

	/**
	 * 执行SQL语句
	 *
	 * @access public
	 *
	 * @param null $sql
	 * @param array $parameters
	 * @return \PDOStatement 返回查询结果,
	 */
	public function execute($sql = null, $parameters = array()) {
		if (empty($sql)) {
			return null;
		}
		EventManger::getInstance()->run('executeSql', $sql);
		try {
			if (!empty($sql)) {
				$this->prepare($sql);
				$this->bind($parameters);
			}
			$this ->result->execute();
		} catch (\PDOException  $ex) {
			$this->error = $ex->getMessage();
		}
		return $this->result;
	}
	
	/**
	 * 得到当前执行语句的错误信息
	 *
	 * @access public
	 *
	 * @return string 返回错误信息,
	 */
	public function getError() {
		return $this->error;
	}

	/**
	 * 获取Object结果集
	 * @param string $sql
	 * @param array $parameters
	 * @return object
	 */
	public function getObject($sql = null, $parameters = array()) {
		$this->execute($sql, $parameters);
		$result = array();
		while (!!$objects = $this->result->fetchObject()) {
			$result[] = $objects;
			
		}
		return $result;
	}

	/**
	 * 获取关联数组
	 * @param string $sql
	 * @param array $parameters
	 * @return array
	 */
	public function getArray($sql = null, $parameters = array()) {
		$this->execute($sql, $parameters);
		return $this->result->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * 事务开始
	 * @return bool
	 */
	public function begin() {
		return $this->driver->beginTransaction();
	}

	/**
	 * 执行事务
	 * @param array $args
	 * @return bool
	 * @throws \Exception
	 */
	public function commit($args = array()) {
		foreach ($args as $item) {
			$this->driver->exec($item);
		}
		return $this->driver->commit();
	}

	/**
	 * 事务回滚
	 * @return bool
	 */
	public function rollBack() {
		return $this->driver->rollBack();
	}
}