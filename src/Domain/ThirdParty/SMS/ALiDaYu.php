<?php
namespace Zodream\Domain\ThirdParty\SMS;

use Zodream\Domain\ThirdParty\ThirdParty;

class ALiDaYu extends ThirdParty {
    protected $apiMap = [
        'send' => [
            'http://gw.api.taobao.com/router/rest',
            [
                'method' => 'alibaba.aliqin.fc.sms.num.send',
                '#app_key',
                'target_app_key',
                'sign_method' => 'md5',
                'sign',
                'session',
                '#timestamp',
                'format' => 'json',
                'v' => '2.0',
                'partner_id',
                'simplify',


                'extend',
                'sms_type' => 'normal',
                '#sms_free_sign_name',
                'sms_param',   //值必须为字符串
                '#rec_num',
                '#sms_template_code'
            ],
            'POST'
        ]
    ];

    protected function getPostData($name, array $args) {
        $data = parent::getPostData($name, $args);
        $data['sign'] = $this->sign($data);
        return $data;
    }

    public function send($templateId, $data, $signName = '阿里大于') {
        $args = $this->getJson('send', [
            'sms_template_code' => $templateId,
            'sms_free_sign_name' => $signName,
            'sms_param' => is_array($data) ? json_encode($data) : $data
        ]);
        if (array_key_exists('error_response', $args)) {
            throw new \Exception($args['error_response']['msg']);
        }
        return array_key_exists('alibaba_aliqin_fc_sms_num_send_response', $args);
    }

    public function sign(array $data) {
        $secret = $this->get('secret');
        if (empty($secret)) {
            throw  new \ErrorException('SECRET ERROR!');
        }
        ksort($data);
        reset($data);
        $arg = '';
        foreach ($data as $key => $item) {
            if ($this->checkEmpty($item) || $key == 'sign') {
                continue;
            }
            $arg .= $key.$item;
        }
        return strtoupper(md5($secret.implode('', $arg).$secret));
    }
}