<?php
namespace Zodream\Domain\ThirdParty\WeChat;

/**
 * Created by PhpStorm.
 * User: zx648
 * Date: 2016/8/22
 * Time: 19:33
 */
use Zodream\Domain\Response\BaseResponse;
use Zodream\Domain\Response\ResponseResult;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;

class MessageResponse extends BaseResponse {
    const TEXT = 'text';
    const IMAGE = 'image';
    const VOICE = 'voice';
    const VIDEO = 'video';
    const MUSIC = 'music';
    const NEWS = 'news';

    protected $data = [];

    public function __construct() {
        $this->setCreateTime(time());
    }

    public function setData($name, $value, $isCData = true) {
        if ($isCData && !is_array($value)) {
            $value = [
                '@cdata' => $value
            ];
        }
        $this->data[$name] = $value;
        return $this;
    }

    public function addData($name, $value) {
        if (!array_key_exists($name, $this->data)) {
            $this->data[$name] = ['item' => []];
        }
        $this->data[$name]['item'][] = $value;
        return $this;
    }


    public function setType($arg) {
        return $this->setData('MsgType', $arg);
    }

    public function setText($arg) {
        return $this->setType(self::TEXT)->setData('Content', $arg);
    }

    public function setImage($mediaId) {
        return $this->setType(self::IMAGE)->setData('Image', [
            'MediaId' => [
                '@cdata' => $mediaId
            ]
        ]);
    }

    public function setVoice($mediaId) {
        return $this->setType(self::VOICE)->setData('Voice', [
            'MediaId' => [
                '@cdata' => $mediaId
            ]
        ]);
    }

    public function setVideo($mediaId, $title = null, $description = null) {
        $data = [
            'MediaId' => [
                '@cdata' => $mediaId
            ]
        ];
        if (!empty($title)) {
            $data['Title'] = [
                '@cdata' => $title
            ];
        }
        if (!empty($description)) {
            $data['Description'] = [
                '@cdata' => $description
            ];
        }
        return $this->setType(self::VIDEO)->setData('Video', $data);
    }

    public function setMusic($mediaId,
                             $title = null,
                             $description = null,
                             $musicUrl = null,
                             $hQMusicUrl = null,
                             $thumbMediaId = null) {
        $data = [
            'MediaId' => [
                '@cdata' => $mediaId
            ]
        ];
        if (!empty($title)) {
            $data['Title'] = [
                '@cdata' => $title
            ];
        }
        if (!empty($description)) {
            $data['Description'] = [
                '@cdata' => $description
            ];
        }
        if (!empty($musicUrl)) {
            $data['MusicUrl'] = [
                '@cdata' => $musicUrl
            ];
        }
        if (!empty($hQMusicUrl)) {
            $data['HQMusicUrl'] = [
                '@cdata' => $hQMusicUrl
            ];
        }
        if (!empty($thumbMediaId)) {
            $data['ThumbMediaId'] = [
                '@cdata' => $thumbMediaId
            ];
        }
        return $this->setType(self::MUSIC)->setData('Music', $data);
    }

    public function setNews(array $arg) {
        if (!array_key_exists('item', $arg)) {
            $arg['item'] = $arg;
        }
        $arg['item'] = array_map(function ($item) {
            if (is_array($item)) {
                return $item;
            }
            return [
                '@cdata' => $item
            ];
        }, $arg['item']);
        return $this->setType(self::NEWS)
            ->setData('ArticleCount', count($arg['item']), false)
            ->setData('Articles', $arg);
    }

    public function addNews($title = null,
                            $description = null,
                            $picUrl = null,
                            $url = null) {
        $data = [];
        if (!empty($title)) {
            $data['Title'] = [
                '@cdata' => $title
            ];
        }
        if (!empty($description)) {
            $data['Description'] = [
                '@cdata' => $description
            ];
        }
        if (!empty($picUrl)) {
            $data['PicUrl'] = [
                '@cdata' => $picUrl
            ];
        }
        if (!empty($url)) {
            $data['Url'] = [
                '@cdata' => $url
            ];
        }
        $this->data['ArticleCount'] ++;
        return $this->addData('Articles', $data);
    }

    public function setToUseName($arg) {
        return $this->setData('ToUserName', $arg);
    }

    public function setFromUseName($arg) {
        return $this->setData('FromUserName', $arg);
    }

    public function setCreateTime($arg) {
        return $this->setData('CreateTime', $arg, false);
    }

    public function sendContent() {
        ResponseResult::make($this->makeXml(), 'xml');
        return parent::sendContent();
    }

    protected function makeXml() {
        return XmlExpand::encode($this->data, 'xml');
    }
}