<?php
namespace Zodream\Module\OAuth\Domain;

use Zodream\Infrastructure\ObjectExpand\TimeExpand;
/**
 * Class OauthClientModel
 * @package Zodream\Module\OAuth\Domain
 * @property string $access_token
 * @property string $client_id
 * @property string $user_id
 * @property string $expires
 * @property string $scope
 */
class OAuthAccessTokenModel extends BaseModel {
    protected $primaryKey = ['access_token'];

    public static function tableName() {
        return 'oauth_access_token';
    }

    public static function createTable() {
        $table = static::getTable();
        $table->set('access_token')->varchar(40)->pk();
        $table->set('client_id')->notNull()->varchar(80);
        $table->set('user_id')->varchar(255);
        $table->set('expires')->notNull()->timestamp();
        $table->set('scope')->varchar(200);
        return $table->create();
    }

    public function refreshToken() {
        if (!$this->delete()) {
            return false;
        }
        $this->isNewRecord = true;
        $this->access_token = $this->generateAccessToken();
        $this->expires = TimeExpand::timestamp(time() + 3600);
        return $this->save();
    }

    /**
     * @param $client_id
     * @param $user_id
     * @return static
     */
    public static function createToken($client_id, $user_id) {
        static::where(['client_id' => $client_id, 'user_id' => $user_id])
            ->delete();
        return static::create([
            'access_token' => static::generateAccessToken(),
            'user_id' => $user_id,
            'client_id' => $client_id,
            'expires' => TimeExpand::timestamp(time() + 3600)
        ]);
    }
}