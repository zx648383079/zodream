<?php
namespace Zodream\Domain\ThirdParty\WeChat;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/23
 * Time: 19:17
 */
class Template extends BaseWeChat {
    protected $apiMap = [
        'setIndustry' => [
            'https://api.weixin.qq.com/cgi-bin/template/api_set_industry',
            '#access_token'
        ],
        'getIndustry' => [
            'https://api.weixin.qq.com/cgi-bin/template/get_industry',
            '#access_token'
        ],
        'addTemplate' => [
            'https://api.weixin.qq.com/cgi-bin/template/api_add_template',
            '#access_token'
        ],
        'all' => [
            'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template',
            '#access_token'
        ],
        'delete' => [
            'https://api,weixin.qq.com/cgi-bin/template/del_private_template',
            '#access_token'
        ],
        'send' => [
            'https://api.weixin.qq.com/cgi-bin/message/template/send',
            '#access_token'
        ]
    ];

    public function setIndustry($id1, $id2) {
        return $this->jsonPost('setIndustry', [
            'industry_id1' => $id1,
            'industry_id2' => $id2
        ]);
    }

    /**
     * @return array [primary_industry, secondary_industry]
     */
    public function getIndustry() {
        return $this->getJson('setIndustry');
    }

    /**
     * @param $id
     * @return bool|string template_id
     */
    public function addTemplate($id) {
        $args = $this->jsonPost('addTemplate', [
            'template_id_short' => $id
        ]);
        if ($args['errcode'] == 0) {
            return $args['template_id'];
        }
        return false;
    }

    public function allTemplate() {
        return $this->getJson('all');
    }

    public function deleteTemplate($id) {
        $args = $this->jsonPost('delete', [
            'template_id' => $id
        ]);
        return $args['errcode'] == 0;
    }

    /**
     * @param $toUser
     * @param $template
     * @param $url
     * @param array $data   [key => [value, color]]
     * @return bool
     */
    public function send($toUser, $template, $url, array $data) {
        $arg = $this->jsonPost('send', [
            'touser' => $toUser,
            'template_id' => $template,
            'url' => $url,
            'data' => $data
        ]);
        return $arg['errcode'] == 0;
    }

}