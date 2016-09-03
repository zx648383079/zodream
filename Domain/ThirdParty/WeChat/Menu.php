<?php
namespace Zodream\Domain\ThirdParty\WeChat;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/19
 * Time: 22:31
 */
class Menu extends BaseWeChat {
    protected $apiMap = [
        'create' => [
            'https://api.weixin.qq.com/cgi-bin/menu/create',
            [
                '#access_token'
            ]
        ],
        'get' => [
            'https://api.weixin.qq.com/cgi-bin/menu/get',
            [
                '#access_token'
            ]
        ],
        'delete' => [
            'https://api.weixin.qq.com/cgi-bin/menu/delete',
            '#access_token'
        ],
        'getMenuInfo' => [
            'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info',
            '#access_token'
        ]
    ];


    /**
     * CREATE MENU
     * @param MenuItem $menu
     * @return bool
     */
    public function create(MenuItem $menu) {
        $args = $this->jsonPost('create', $menu->toArray());
        if ($args['errcode'] == 0) {
            return true;
        }
        $this->error = $args['errmsg'];
        return false;
    }

    public function getMenu() {
        return $this->getJson('get');
    }

    public function deleteMenu() {
        $arg = $this->getJson('delete');
        return is_array($arg) && $arg['errcode'] == 0;
    }
}