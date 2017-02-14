<?php
namespace Zodream\Module\OAuth\Domain;

/**
 * Class OauthClientModel
 * @package Zodream\Module\OAuth\Domain
 * @property string $client_id
 * @property string $client_secret
 * @property string $redirect_uri
 */
class OauthClientModel extends BaseModel {
    public static function tableName() {
        return 'oauth_client';
    }

    public static function createTable() {
        $table = static::getTable();
        $table->set('client_id')->varchar(80)->pk();
        $table->set('client_secret')->notNull()->varchar(80);
        $table->set('redirect_uri')->notNull()->varchar(200);
        return $table->create();
    }
}