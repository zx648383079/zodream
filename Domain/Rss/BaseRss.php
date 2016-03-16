<?php
namespace Zodream\Domain\Rss;
/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/3/16
 * Time: 21:06
 */
abstract class BaseRss {
    protected $tags = array();
    protected $pubDate;
    protected $title;
    protected $link;
    protected $description;

    public function setLink($link) {
        $this->link = $link;
        return $this;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    public function setDescription($value) {
        $this->description = $value;
        return $this;
    }

    public function setPubDate($time) {
        if(strtotime($time) == false) {
            $this->pubDate = date('D, d M Y H:i:s ', $time) . 'GMT';
        } else {
            $this->pubDate = date('D, d M Y H:i:s ', strtotime($time)) . 'GMT';
        }
    }

    public function getPubDate() {
        if(empty($this->pubDate)) {
            return date('D, d M Y H:i:s ') . 'GMT';
        }
        return $this->pubDate;
    }

    public function addTag($tag, $value) {
        $this->tags[$tag] = $value;
        return $this;
    }

    abstract public function toString();
}