<?php
namespace Zodream\Domain\OAuth;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/4/10
 * Time: 15:25
 *
 *
 */
use Zodream\Domain\Response\Redirect;
use Zodream\Infrastructure\Error;
use Zodream\Infrastructure\Request;
use Zodream\Infrastructure\Session;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
class QQ extends BaseOAuth {

    const VERSION = "2.0";
    const GET_AUTH_CODE_URL = "https://graph.qq.com/oauth2.0/authorize";
    const GET_ACCESS_TOKEN_URL = "https://graph.qq.com/oauth2.0/token";
    const GET_OPENID_URL = "https://graph.qq.com/oauth2.0/me";

    /********
    配置 appid appkey callback scope
     * @var string
     */
    protected $config = 'qq';

    protected $apiMap = array(
        /*                       qzone                    */
        'add_blog' => array(
            'https://graph.qq.com/blog/add_one_blog',
            array('title', 'format' => 'json', 'content' => null),
            'POST'
        ),
        'add_topic' => array(
            'https://graph.qq.com/shuoshuo/add_topic',
            array('richtype','richval','con','#lbs_nm','#lbs_x','#lbs_y','format' => 'json', '#third_source'),
            'POST'
        ),
        'get_user_info' => array(
            'https://graph.qq.com/user/get_user_info',
            array('format' => 'json'),
            'GET'
        ),
        'add_one_blog' => array(
            'https://graph.qq.com/blog/add_one_blog',
            array('title', 'content', 'format' => 'json'),
            'GET'
        ),
        'add_album' => array(
            'https://graph.qq.com/photo/add_album',
            array('albumname', '#albumdesc', '#priv', 'format' => 'json'),
            'POST'
        ),
        'upload_pic' => array(
            'https://graph.qq.com/photo/upload_pic',
            array('picture', '#photodesc', '#title', '#albumid', '#mobile', '#x', '#y', '#needfeed', '#successnum', '#picnum', 'format' => 'json'),
            'POST'
        ),
        'list_album' => array(
            'https://graph.qq.com/photo/list_album',
            array('format' => 'json')
        ),
        'add_share' => array(
            'https://graph.qq.com/share/add_share',
            array('title', 'url', '#comment','#summary','#images','format' => 'json','#type','#playurl','#nswb','site','fromurl'),
            'POST'
        ),
        'check_page_fans' => array(
            'https://graph.qq.com/user/check_page_fans',
            array('page_id' => '314416946','format' => 'json')
        ),
        /*                    wblog                             */

        'add_t' => array(
            'https://graph.qq.com/t/add_t',
            array('format' => 'json', 'content','#clientip','#longitude','#compatibleflag'),
            'POST'
        ),
        'add_pic_t' => array(
            'https://graph.qq.com/t/add_pic_t',
            array('content', 'pic', 'format' => 'json', '#clientip', '#longitude', '#latitude', '#syncflag', '#compatiblefalg'),
            'POST'
        ),
        'del_t' => array(
            'https://graph.qq.com/t/del_t',
            array('id', 'format' => 'json'),
            'POST'
        ),
        'get_repost_list' => array(
            'https://graph.qq.com/t/get_repost_list',
            array('flag', 'rootid', 'pageflag', 'pagetime', 'reqnum', 'twitterid', 'format' => 'json')
        ),
        'get_info' => array(
            'https://graph.qq.com/user/get_info',
            array('format' => 'json')
        ),
        'get_other_info' => array(
            'https://graph.qq.com/user/get_other_info',
            array('format' => 'json', '#name', 'fopenid')
        ),
        'get_fanslist' => array(
            'https://graph.qq.com/relation/get_fanslist',
            array('format' => 'json', 'reqnum', 'startindex', '#mode', '#install', '#sex')
        ),
        'get_idollist' => array(
            'https://graph.qq.com/relation/get_idollist',
            array('format' => 'json', 'reqnum', 'startindex', '#mode', '#install')
        ),
        'add_idol' => array(
            'https://graph.qq.com/relation/add_idol',
            array('format' => 'json', '#name-1', '#fopenids-1'),
            'POST'
        ),
        'del_idol' => array(
            'https://graph.qq.com/relation/del_idol',
            array('format' => 'json', '#name-1', '#fopenid-1'),
            'POST'
        ),
        /*                           pay                          */

        'get_tenpay_addr' => array(
            'https://graph.qq.com/cft_info/get_tenpay_addr',
            array('ver' => 1,'limit' => 5,'offset' => 0,'format' => 'json')
        )
    );


    /**
     * 调用相应api
     * @param array $arr
     * @param array $argsList
     * @param string $baseUrl
     * @param string $method
     * @return mixed|null|string
     */
    protected function getByApi($arr, $argsList, $baseUrl, $method) {
        $pre = "#";
        $keysArr = $this->keysArr;
        $optionArgList = array();//一些多项选填参数必选一的情形
        foreach ($argsList as $key => $val) {
            $tmpKey = $key;
            $tmpVal = $val;
            if (!is_string($key)) {
                $tmpKey = $val;
                if(strpos($val,$pre) === 0){
                    $tmpVal = $pre;
                    $tmpKey = substr($tmpKey,1);
                    if(preg_match("/-(\d$)/", $tmpKey, $res)){
                        $tmpKey = str_replace($res[0], '', $tmpKey);
                        $optionArgList[$res[1]][] = $tmpKey;
                    }
                }else{
                    $tmpVal = null;
                }
            }
            //-----如果没有设置相应的参数
            if (!isset($arr[$tmpKey]) || $arr[$tmpKey] === '') {
                if ($tmpVal == $pre) {//则使用默认的值
                    continue;
                }
                if ($tmpVal) {
                    $arr[$tmpKey] = $tmpVal;
                    continue;
                }
                if($v = $_FILES[$tmpKey]){
                    $filename = dirname($v['tmp_name'])."/".$v['name'];
                    move_uploaded_file($v['tmp_name'], $filename);
                    $arr[$tmpKey] = "@$filename";
                    continue;
                }
                Error::out("api调用参数错误,未传入参数$tmpKey", __FILE__, __LINE__);
            }
            $keysArr[$tmpKey] = $arr[$tmpKey];
        }
        //检查选填参数必填一的情形
        foreach ($optionArgList as $val) {
            $n = 0;
            foreach ($val as $v){
                if (in_array($v, array_keys($keysArr))) {
                    $n ++;
                }
            }
            if (!$n) {
                Error::out('api调用参数错误'.implode(',', $val).'必填一个', __FILE__, __LINE__);
            }
        }
        $response = null;
        if($method == "POST"){
            if($baseUrl == "https://graph.qq.com/blog/add_one_blog") {
                $response = $this->httpPost($baseUrl, $keysArr, 1);
            }
            else {
                $response = $this->httpPost($baseUrl, $keysArr, 0);
            }
        } else if($method == "GET") {
            $response = $this->httpGet($baseUrl, $keysArr);
        }
        return $response;
    }

    /**
     * _call
     * 魔术方法，做api调用转发
     * @param string $name    调用的方法名称
     * @param array $arg      参数列表数组
     * @since 5.0
     * @return array          返加调用结果数组
     */
    public function __call($name, $arg) {
        $content = parent::__call($name, $arg);
        //对于get_tenpay_addr，特殊处理，php json_decode对\xA312此类字符支持不好
        if($name != 'get_tenpay_addr'){
            $responseArr = $this->objectToArray(json_decode($content));
        }else{
            $responseArr = $this->jsonParser($content);
        }
        //检查返回ret判断api是否成功调用
        if ($responseArr['ret'] == 0){
            return $responseArr;
        }
        Error::out($responseArr['ret'] . $responseArr['msg'], __FILE__, __LINE__);
        return null;
    }

    public function login() {
        $appid = $this->get('appid');
        $callback = $this->get('callback');
        $scope = $this->get('scope');
        //-------生成唯一随机串防CSRF攻击
        $state = md5(uniqid(rand(), TRUE));
        Session::setValue('state', $state);

        //-------构造请求参数列表
        $keysArr = array(
            'response_type' => 'code',
            'client_id' => $appid,
            'redirect_uri' => $callback,
            'state' => $state,
            'scope' => $scope
        );
        Redirect::to(StringExpand::urlBindValue(self::GET_AUTH_CODE_URL, $keysArr));
    }

    public function callback() {
        $state = Session::getValue('state');

        //--------验证state防止CSRF攻击
        if(Request::get('state') != $state){
            Error::out('CSRF 错误！', __FILE__, __LINE__);
        }

        //-------请求参数列表
        $keysArr = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->get('appid'),
            'redirect_uri' => urlencode($this->get('callback')),
            'client_secret' => $this->get('appkey'),
            'code' => Request::get('code')
        );

        //------构造请求access_token的url
        $response = $this->httpGet(self::GET_ACCESS_TOKEN_URL, $keysArr);

        if (strpos($response, 'callback') !== false){
            $lpos = strpos($response, '(');
            $rpos = strrpos($response, ')');
            $response = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);
            if(isset($msg->error)){
                Error::out($msg->error. $msg->error_description, __FILE__, __LINE__);
            }
        }

        $params = array();
        parse_str($response, $params);
        Session::setValue('access_token', $params['access_token']);
        return $params['access_token'];
    }

    public function getOpenId() {
        //-------请求参数列表
        $keysArr = array(
            'access_token' => Session::getValue('access_token')
        );

        $response = $this->httpGet(self::GET_OPENID_URL, $keysArr);

        //--------检测错误是否发生
        if (strpos($response, 'callback') !== false) {
            $lpos = strpos($response, '(');
            $rpos = strrpos($response, ')');
            $response = substr($response, $lpos + 1, $rpos - $lpos -1);
        }

        $user = json_decode($response);
        if(isset($user->error)){
            Error::out($user->error. $user->error_description, __FILE__, __LINE__);
        }

        //------记录openid
        Session::setValue('openid', $user->openid);
        return $user->openid;
    }
}