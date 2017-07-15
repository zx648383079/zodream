<?php
namespace Zodream\Module\OAuth\Domain;

use Zodream\Domain\Model\Model;
use Zodream\Infrastructure\Database\Schema\Table;
use Zodream\Infrastructure\ObjectExpand\StringExpand;

/**
 * Class OauthClientModel
 * @package Zodream\Module\OAuth\Domain
 *
 */
abstract class BaseModel extends Model {

    public static function getTable() {
        return new Table(static::tableName());
    }

    abstract public static function createTable();

    /**
     * 删除表
     * @return mixed
     */
    public static function dropTable() {
        return static::getTable()->drop();
    }

    /**
     * 生成 access token
     * @return string
     */
    public static function generateAccessToken() {
        return bin2hex(StringExpand::randomBytes(20));
    }

    public function isExpire() {
        return strtotime($this->expires) >= time();
    }
}