<?php
namespace Zodream\Domain\ThirdParty\WeChat;
/**
 * 群发
 * @package Zodream\Domain\ThirdParty\WeChat
 */
class Mass extends BaseWeChat {
    const NEWS = 'mpnews';
    const TEXT = 'text';
    const VOICE = 'voice';
    const IMAGE = 'image';
    const VIDEO = 'mpvideo';
    const CARD = 'wxcard';

    protected $apiMap = [
        'uploadImg' => [
            [
                'https://api.weixin.qq.com/cgi-bin/media/uploadimg',
                '#access_token'
            ],
            '#media',
            'POST'
        ],
        'uploadNews' => [
            [
                'https://api.weixin.qq.com/cgi-bin/media/uploadnews',
                '#access_token'
            ],
            '#articles',
            'POST'
        ],
        'sendAll' => [
            [
                'https://api.weixin.qq.com/cgi-bin/message/mass/sendall',
                '#access_token'
            ],
            [
                '#filter',
                '#msgtype',
                'mpnews',
                'text',
                'voice',
                'image',
                'mpvideo',
                'wxcard'
            ],
            'POST'
        ],
        'send' => [
            [
                'https://api.weixin.qq.com/cgi-bin/message/mass/send',
                '#access_token'
            ],
            [
                '#touser',
                '#msgtype',
                'mpnews',
                'text',
                'voice',
                'image',
                'mpvideo',
                'wxcard'
            ]
        ],
        'delete' => [
            [
                'https://api.weixin.qq.com/cgi-bin/message/mass/delete',
                '#access_token'
            ],
            '#msg_id'
        ],
        'preview' => [
            [
                'https://api.weixin.qq.com/cgi-bin/message/mass/preview',
                '#access_token'
            ],
            [
                '#touser',
                '#msgtype',
                'mpnews',
                'text',
                'voice',
                'image',
                'mpvideo',
                'wxcard'
            ]
        ],
        'query' => [
            [
                'https://api.weixin.qq.com/cgi-bin/message/mass/get',
                '#access_token'
            ],
            '#msg_id'
        ],
    ];

    public function uploadImg($file) {
        $args = $this->getJson('uploadImg', [
            'media' => '@'.$file
        ]);
        return array_key_exists('url', $args) ? $args['url'] : false;
    }

    public function updateNews(NewsItem $news) {
        $args = $this->getJson('updateNews', $news->toArray());
        return $args['errcode'] == 0;
    }

    public function sendAll(array $data, $groupId = null) {
        $data['filter'] =  empty($groupId) ? [
            'is_to_all' => true
        ] : [
            'is_to_all' => false,
            'group_id' => $groupId
        ];
        $args = $this->getJson('sendAll', $data);
        if ($args['errcode'] === 0) {
            return $args['msg_id'];
        }
        throw  new \Exception($args['errmsg']);
    }

    public function send(array $openId, array $data) {
        $data['touser'] = array_values($openId);
        $args = $this->getJson('send', $data);
        if ($args['errcode'] === 0) {
            return $args['msg_id'];
        }
        throw  new \Exception($args['errmsg']);
    }

    public function cancel($msgId) {
        $args = $this->getJson('delete', [
            'msg_id' => $msgId
        ]);
        if ($args['errcode'] === 0) {
            return true;
        }
        throw  new \Exception($args['errmsg']);
    }

    public function preview($openId, array $data) {
        $data['touser'] = $openId;
        $args = $this->getJson('query', $data);
        if ($args['errcode'] === 0) {
            return true;
        }
        throw  new \Exception($args['errmsg']);
    }

    public function query($msgId) {
        $args = $this->getJson('query', [
            'msg_id' => $msgId
        ]);
        if (isset($args['msg_status'])) {
            return $args['msg_status'];
        }
        return false;
    }
}