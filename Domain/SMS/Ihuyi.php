<?php
namespace Zodream\Domain\SMS;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/5/12
 * Time: 15:13
 */
use Zodream\Infrastructure\Config;
use Zodream\Infrastructure\Http\Http;
use Zodream\Infrastructure\MagicObject;

/**
 * Class Ihuyi
 * http://www.ihuyi.com/
 * @package Zodream\Domain
 * @property string $template 短信模板 {code} 代替验证码
 * @property string $account 账户
 * @property string $password 密码 可以是32位md5加密过的
 */
class Ihuyi extends MagicObject {

    const Url = 'http://106.ihuyi.cn/webservice/sms.php?format=json&method=';

    protected $_data = array(
        'template' => '您的验证码是：{code}。请不要把验证码泄露给其他人。'
    );

    protected $http;

    //protected $errorNo;

    protected $error = null;

    public function __construct() {
        $this->set(Config::getValue('sms'));
        $this->http = new Http();
    }

    /**
     * 发送验证短信
     * @param sting|integer $mobile
     * @param string|integer $code
     * @return bool|integer false|短信ID
     */
    public function send($mobile, $code) {
        $data = json_decode($this->http->post(self::Url.'Submit', array(
            'account' => $this->get('account'),
            'password' => $this->get('password'),
            'mobile' => $mobile,
            'content' => str_replace('{code}', $code, $this->get('template'))
        )), true);
        if ($data['code'] == 2) {
            return $data['smsid'];
        }
        //$this->errorNo = $data['code'];
        $this->error = $data['msg'];
        return false;
    }

    /**
     * 余额查询
     * @return bool|integer
     */
    public function balance() {
        $data = json_decode($this->http->post(self::Url.'GetNum', array(
            'account' => $this->get('account'),
            'password' => $this->get('password')
        )), true);
        if ($data['code'] == 2) {
            return $data['num'];
        }
        $this->error = $data['msg'];
        return false;
    }

    public function getError() {
        return $this->error;
    }
}