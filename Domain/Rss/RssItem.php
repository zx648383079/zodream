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
    protected $giud;
    protected $attachment;
    protected $length;
    protected $mimeType;

    public function setGiud($giud) {
        $this->giud = $giud;
        return $this;
    }

    public function toString() {
        $out = "<item>\n";
        $out .= '<title>' . $this->title . "</title>\n";
        $out .= '<link>' . $this->link . "</link>\n";
        $out .= '<description>' . $this->description . "</description>\n";
        $out .= '<pubDate>' . $this->getPubDate() . "</pubDate>\n";
        if($this->attachment != '') {
            $out .= "<enclosure url='{$this->attachment}' length='{$this->length}' type='{$this->mimeType}' />";
        }
        if(empty($this->giud)) {
            $this->giud = $this->link;
        }
        $out .= '<guid>' . $this->giud . "</guid>\n";
        foreach($this->tags as $key => $val) {
            $out .= "<$key>$val</$key\n>";
        }
        $out .= "</item>\n";
        return $out;
    }

    public function enclosure($url, $mimeType, $length) {
        $this->attachment = $url;
        $this->mimeType  = $mimeType;
        $this->length   = $length;
        return $this;
    }
}