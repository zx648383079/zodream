<?php
namespace Zodream\Domain\Rss;
/**
 *
 *
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/16
 * Time: 20:44
 */
class RssItem extends BaseRss {
    protected $guid;
    protected $attachment;
    protected $length;
    protected $mimetype;

    public function setGuid($guid) {
        $this->guid = $guid;
        return $this;
    }

    public function toString() {
        $out = "<item>\n";
        $out .= '<title>' . $this->title . "</title>\n";
        $out .= '<link>' . $this->link . "</link>\n";
        $out .= '<description>' . $this->description . "</description>\n";
        $out .= '<pubDate>' . $this->getPubDate() . "</pubDate>\n";
        if($this->attachment != '') {
            $out .= "<enclosure url='{$this->attachment}' length='{$this->length}' type='{$this->mimetype}' />";
        }
        if(empty($this->guid)) {
            $this->guid = $this->link;
        }
        $out .= '<guid>' . $this->guid . "</guid>\n";
        foreach($this->tags as $key => $val) {
            $out .= "<$key>$val</$key\n>";
        }
        $out .= "</item>\n";
        return $out;
    }

    public function enclosure($url, $mimetype, $length) {
        $this->attachment = $url;
        $this->mimetype  = $mimetype;
        $this->length   = $length;
        return $this;
    }
}