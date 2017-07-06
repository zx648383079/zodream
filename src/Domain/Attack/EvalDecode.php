<?php
namespace Zodream\Domain\Attack;
/**
解码 eval 加密的
*/
class EvalDecode {
    protected $sourse;

    protected $include = <<<EOF
        \$pos = strpos(\$this->sourse, 'eval');
        if (\$pos === false) {
            //没找到
            return \$this->sourse;
       }
       \$str = str_ireplace('eval', '\$this->set_sourse', \$this->sourse);
        eval(\$str.\$this->include);
EOF;

    public function __construct($sourse){
        $this->sourse = $sourse;
    }

    public function decode(){
        $pos = strpos($this->sourse,'eval');
        if ($pos === false) {
            //没找到
            return $this->sourse;
        }
        $str = str_ireplace('eval', '$this->set_sourse', $this->sourse);
        eval($str. $this->include);
    }

    public function setSourse($sourse){
        $this->sourse = $sourse;
        return $this;
    }
}
