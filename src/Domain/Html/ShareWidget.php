<?php
namespace Zodream\Domain\Html;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/6/2
 * Time: 16:32
 */
class ShareWidget extends Widget {

    protected $_data = array(
        'text' => null,
        'type' => 'slide',
        'left' => true,
        'template' => '<div class="bdsharebuttonbox">
<a href="#" class="bds_more" data-cmd="more"></a>
<a href="#" class="bds_qzone" data-cmd="qzone" title="分享到QQ空间"></a>
<a href="#" class="bds_tsina" data-cmd="tsina" title="分享到新浪微博"></a>
<a href="#" class="bds_tqq" data-cmd="tqq" title="分享到腾讯微博"></a>
<a href="#" class="bds_renren" data-cmd="renren" title="分享到人人网"></a>
<a href="#" class="bds_weixin" data-cmd="weixin" title="分享到微信"></a></div>'
    );

    private function _getType() {
        if ($this->get('type') === 'slide' || $this->has('slide')) {
            return '"slide":'.$this->json($this->get('slide', array(
                'type' => 'slide',
                'bdImg' => 0,
                'bdPos' => $this->get('left') ? 'left' : 'right',
                'bdTop' => '100'
            )));
        }
        return '"share":'.$this->json($this->get('share', array()));
    }

    private function _getCommon() {
        return '"common":'.$this->json($this->get('common', array(
            'bdSnsKey' => (array)$this->get('key'),
            'bdText' => $this->get('text'),
            'bdMini' => 2,
            'bdMiniList' => false,
            'bdPic' => '',
            'bdStyle' => $this->get('style', 0),
            'bdSize' => $this->get('size', 16)
        )));
    }

    protected function run() {
        $config = $this->_getCommon().','.$this->_getType();
        if ($this->has('image')) {
            $config .= ',"image":'.$this->json($this->get('image'));
        }
        $html = <<<HTML
<script>
window._bd_share_config={
{$config}
};
with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];</script>
HTML;
        if ($this->get('type') != 'slide' && !$this->has('slide')) {
            return $html;
        }
        return $this->get('template').$html;
    }

}