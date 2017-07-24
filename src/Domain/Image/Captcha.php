<?php
namespace Zodream\Domain\Image;
/**
 * 验证码
 *
 * @author Jason
 */
use Zodream\Infrastructure\Security\Hash;
use Zodream\Infrastructure\Traits\ConfigTrait;
use Zodream\Service\Factory;

class Captcha extends WaterMark {

    use ConfigTrait;

    protected $configKey = 'captcha';

    protected $code;

    protected $configs = [
        'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', //随机因子
        'length' => 4,    //验证码长度
        'fontSize' => 30,   //指定字体大小
        'fontColor' => '',   //指定字体颜色, 可以是数组
        'fontFamily' => null,  //指定的字体
        'width' => 100,
        'height' => 30,
        'sensitive' => true,   // 大小写敏感
    ];

	/**
	 * 获取验证码
	 * @return string
	 */
	public function getCode() {
        if (empty($this->code)) {
            $this->createCode();
        }
		return $this->code;
	}

	/**
	 * 生成
	 * @param int $level 干扰等级
	 * @return $this
	 */
	public function generate($level = 0) {
	    $this->getCode();
		$this->loadConfigs();
        $this->width = $this->configs['width'];
        $this->height = $this->configs['height'];
		$this->_createBg();
		$this->_createText();
		$this->_createLine($level);
		return $this;
	}


    /**
     * 生成随机码
     * @return $this
     */
	public function createCode() {
		$charset = $this->configs['characters'];
		$_len   = strlen($charset)-1;
		for ($i = 0; $i < $this->configs['length']; $i ++) {
            $this->code .= $charset[mt_rand(0, $_len)];
        }
        Factory::session()->set('captcha', [
            'sensitive' => $this->configs['sensitive'],
            'key'       => Hash::make($this->configs['sensitive'] ? $this->code : strtolower($this->code))
        ]);
        return $this;
	}
	
	/**
	 * 生成背景
	 */
	private function _createBg() {
		$this->create($this->width, $this->height);
		imagefilledrectangle(
				$this->image, 
				0, 
				$this->height, 
				$this->width, 
				0, 
				$this->getColorWithRGB(mt_rand(157, 255), mt_rand(157, 255), mt_rand(157, 255))
		);
	}
	
	/**
	 * 生成文字
	 */
	private function _createText() {
		$x = $this->width / $this->configs['length'];
		for ($i = 0 ; $i < $this->configs['length']; $i ++) {
			$this->addText(
					$this->code[$i],
					$x * $i + mt_rand(1, 5),
					($this->height - $this->configs['fontSize']) / 2 ,
					$this->configs['fontSize'],
					$this->getColorWithRGB($this->_getTextFont($i)),
					$this->configs['fontFamily'],
					mt_rand(-30, 30)
			);
		}
	}

    /**
     * 获取字体颜色
     * @param integer $i
     * @return array|mixed
     */
	private function _getTextFont($i) {
	    if (empty($this->configs['fontColor'])) {
	        return [mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156)];
        }
        if (!is_array($this->configs['fontColor'])) {
	        return $this->configs['fontColor'];
        }
        if (count($this->configs['fontColor']) <= $i) {
	        return [mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156)];
        }
        return $this->configs['fontColor'][$i];
    }

	/**
	 * 生成线条、雪花
	 * @param int $level
	 */
	private function _createLine($level = 1) {
		//线条
		for ($i = 0; $i < 6; $i ++) {
			imageline(
					$this->image, 
					mt_rand(0, $this->width), 
					mt_rand(0, $this->height), 
					mt_rand(0, $this->width), 
					mt_rand(0, $this->height), 
					$this->getColorWithRGB(mt_rand(0, 156), mt_rand(0, 156), mt_rand(0, 156))
			);
		}
		//雪花
		for ($i = 0, $length = $level * 20; $i < $length; $i ++) {
			imagestring(
					$this->image, 
					mt_rand(1, 5), 
					mt_rand(0, $this->width), 
					mt_rand(0, $this->height), 
					'*', 
					$this->getColorWithRGB(mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255))
			);
		}
	}

    /**
     * 验证
     * @param string $value
     * @return bool
     */
    public function verify($value) {
        if (!Factory::session()->has('captcha')) {
            return false;
        }
        $data = Factory::session()->get('captcha');
        if (!$data['sensitive']) {
            $value = strtolower($value);
        }
        Factory::session()->delete('captcha');
        return Hash::verify($value, $data['key']);
    }
}