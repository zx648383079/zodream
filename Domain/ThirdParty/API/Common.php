<?php
namespace Zodream\Domain\ThirdParty\API;

use Zodream\Infrastructure\ThirdParty;
class Common extends ThirdParty {

    protected $apiMap = array(
        'weather' => array(
            'http://op.juhe.cn/onebox/weather/query',
            array(
                '#cityname',
                '#key',
                'dtype' // 返回数据的格式,xml或json，默认json 
            )
        ),
        'webscan' => array(
            'http://apis.juhe.cn/webscan/',
            array(
                '#domain',
                'dtype' => 'json',  //返回类型,xml/json/jsonp可选
                'callback',
                '#key'
            )
        ),
        'exchange' => array(
            'http://op.juhe.cn/onebox/exchange/query',
            array(
                '#key'
            )
        ),
        'wooyun' => array(
            'http://op.juhe.cn/wooyun/index',
            array(
                '#key',
                'type',   // 查询方式，可选值为submit、confirm、public、unclaim，不提供则默认为查询最新的漏洞 
                'limit',
                'dtype' //json或xml，默认为json
            )
        ),
        'ip' => array(
            'http://apis.juhe.cn/ip/ip2addr',
            array(
                '#key',
                '#ip',
                'dtype' //json xml
            )
        ),
        'kuaidi' => array(
            'http://api.kuaidi100.com/api',
            array(
                '#id',
                '#com', //公司编码
                '#nu',
                'show',  //0：返回json字符串， 1：返回xml对象， 2：返回html对象， 3：返回text文本。 
                'muti',  //1:返回多行完整的信息， 0:只返回一行信息。 不填默认返回多行。
                'order'   //desc：按时间由新到旧排列， asc：按时间由旧到新排列。 不填默认返回倒序（大小写不敏感） 
            )
        ),
        'exchange' => array(
            'http://apis.baidu.com/apistore/currencyservice/currency',
            array(
                '#fromCurrency',
                '#toCurrency',
                '#amount'
            )
        ),
        'sinaIP' => array(
            'http://int.dpool.sina.com.cn/iplookup/iplookup.php',
            array(
                'format' => 'json',
                'ip'
            )
        ),
        'taoBaoIP' => array(
            'http://ip.taobao.com/service/getIpInfo.php',
            array(
                '#ip'
            )
        )
    );


    /**
     * 汇率查询
     * @param string $from
     * @param string $to
     * @param int $amount
     * @return array
     */
    public function exchange($from, $to, $amount = 1) {
        $this->http->setHeader(['apikey' => $this->get('apikey')]);
        return $this->getJson('exchange', array(
            'fromCurrency' => $from,
            'toCurrency' => $to,
            'amount' => $amount
        ));
    }

    public function getAddressByIp($ip = null) {
        if (empty($ip)) {
            return $this->getJson('sinaIP');
        }
        return $this->getJson('taoBaoIP', array(
            'ip' => $ip
        ));
    }
}