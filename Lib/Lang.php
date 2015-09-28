<?php  
namespace App\Lib;
/** 
 * Lang 语言包类 
 * 
 */  
class Lang  
{  
    
    public static $language;
    /** 
     * _options 设置语言包的选项 
     * $this->_options['lang'] 应用程序使用什么语言包.php-gettext支持的所有语言都可以. 
     * 在ubuntu下使用sudo vim /usr/share/il8n/SUPPORTED 主要是utf8编码 
     * $this->_options['domain'] 生成的.mo文件的名字.一般是应用程序名 
     * @var array 
     * @access protected 
     */  
    public static $options;  
  
    /** 
     * 构造函数 
     * 对象初始化是设置语言包的参数 
     * @param string $lang 
     * @access public 
     * @return void 
     */  
    public static function load($lang=null) 
    {  
        switch ( strtolower($lang) ) {  
            case 'zh_cn':  
                self::$options = array('lang' => 'zh_CN.UTF8', 'domain' => 'nways');  
                break;  
            case 'en':  
                self::$options = array('lang' => 'en_US.UTF8', 'domain' => 'nways');  
                break;  
            case 'en_us':  
                self::$options = array('lang' => 'en_US.UTF8', 'domain' => 'nways');  
                break;  
            case 'en_gb':  
                self::$options = array('lang' => 'en_US.UTF8', 'domain' => 'nways');  
                break;  
            default:  
                self::$options = array('lang' => 'zh_CN.UTF8', 'domain' => 'nways');  
            break;  
        }
        self::setLang();  
    }  
  
    /** 
     * 设置应用程序语言包的参数，放在$this->_options中 
     * @param mixed $options 
     * @return void 
     */  
    public static function setOptions($options) 
    {  
        if (!empty($options)) {
            foreach ($options as $key => $option) {  
                self::$options[$key] = $option;  
            }  
        }  
    }  
    
    /**
	* 获取语言类型
	* @access globe
	*
	* @return string 返回语言,
	*/
	public static function getLang() 
    {
        if( empty( self::$language ))
        {
            $language = $_SERVER ['HTTP_ACCEPT_LANGUAGE'];  
            preg_match_all ( "/[\w-]+/", $language, $language );  
            self::$language = $language [0] [0];
        }
		return self::$language;
	}

    /**
     * 设置应用程序语言包
     * @access public
     * @param null $lang 语言
     */
    public static function setLang( $lang = null) 
    {  
        if(empty(self::$options))
        {
            if(empty($lang))
            {
                $lang = self::getLang();
            }
            self::load($lang);
        }
        
        putenv('LANG='.self::$options['lang']);  
        putenv('LANGUAGE='.self::$options['lang']);  
        setlocale(LC_ALL, self::$options['lang']);  
        bindtextdomain(self::$options['domain'], 'asset/lang/');  
        textdomain(self::$options['domain']);  
        bind_textdomain_codeset(self::$options['domain'], 'UTF-8');  
    }  
  
}  