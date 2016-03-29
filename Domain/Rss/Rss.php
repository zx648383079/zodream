<?php
namespace Zodream\Domain\Rss;
use Zodream\Domain\Response\ResponseResult;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/16
 * Time: 20:42
 */
class Rss extends BaseRss {
    protected $language = "zh-CN";
    protected $items = array();

    public function setLanguage($value) {
        $this->language = $value;
        return $this;
    }

    public function addItem(RssItem $item) {
        $this->items[] = $item;
        return $this;
    }

    public function toString() {
        $out = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $out .= '<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";

        $out .= "<channel>\n";
        $out .= '<title>' . $this->title . "</title>\n";
        $out .= '<link>' . $this->link . "</link>\n";
        $out .= '<description>' . $this->description . "</description>\n";
        $out .= '<language>' . $this->language . "</language>\n";
        $out .= '<pubDate>' . $this->getPubDate() . "</pubDate>\n";
        foreach($this->tags as $key => $val) {
            $out .= "<$key>$val</$key>\n";
        }
        foreach($this->items as $item) {
            $out .= $item->show();
        }
        $out .= "</channel>\n";
        $out .= '</rss>';
        return $out;
    }

    public function Show() {
        ResponseResult::make($this->toString(), 'rss');
    }
}