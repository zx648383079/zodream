<?php
defined('APP_DIR') or exit();
use Zodream\Domain\View\View;
use Zodream\Infrastructure\Disk\File;
use Zodream\Infrastructure\ObjectExpand\StringExpand;
/** @var $this Zodream\Domain\View\View */
/** @var $file \Zodream\Infrastructure\Disk\File */
require $file->getFullName();
if (!class_exists($className)) :
    die("Error: cannot find class($className). \n");
endif;
$reflector = new ReflectionClass($className);

$methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

$objName = StringExpand::studly($className);
echo '<?php';
?>
/**
 * PhpUnderControl" . $objName. "Test
 *
 * 针对 $filePath $className 类的PHPUnit单元测试
 *
 * @author: zodream <?=date('Ymd')?>
 */

//require_once dirname(__FILE__) . '/test_env.php';

<?php
$initWay = "new $className()";
if (method_exists($className, '__construct')) :
    $constructMethod = new ReflectionMethod($className, '__construct');
    if (!$constructMethod->isPublic()) :
        if (is_callable(array($className, 'getInstance'))) :
            $initWay = "$className::getInstance()";
        elseif(is_callable(array($className, 'newInstance'))) :
            $initWay = "$className::newInstance()";
        else:
            $initWay = 'NULL';
        endif;
    endif;
endif;
?>
use PHPUnit_Framework_TestCase;

class <?=$objName?>Test extends PHPUnit_Framework_TestCase {

    public $instance;

    protected function setUp() {
        parent::setUp();
        $this->$instance = <?=$initWay?>;
    }

    protected function tearDown() {

    }

<?php foreach ($methods as $method) : ?>
    <?php if($method->class != $className) continue;

    $fun = $method->name;
    $Fun = ucfirst($fun);

    if (strlen($Fun) > 2 && substr($Fun, 0, 2) == '__') continue;

    $rMethod = new ReflectionMethod($className, $method->name);
    $params = $rMethod->getParameters();
    $isStatic = $rMethod->isStatic();
    $isConstructor = $rMethod->isConstructor();

    if ($isConstructor) continue;

    $initParamStr = '';
    $callParamStr = '';
    foreach ($params as $param) :
        $default = '';

        $rp = new ReflectionParameter(array($className, $fun), $param->name);
        if ($rp->isOptional()) {
            $default = $rp->getDefaultValue();
        }
        if (is_string($default)) {
            $default = "'$default'";
        } else if (is_array($default)) {
            $default = var_export($default, true);
        } else if (is_bool($default)) {
            $default = $default ? 'true' : 'false';
        } else if ($default === null) {
            $default = 'null';
        } else {
            $default = "''";
        }

        $initParamStr .= "
        \$" . $param->name . " = $default;";
        $callParamStr .= '$' . $param->name . ', ';
    endforeach;
    $callParamStr = empty($callParamStr) ? $callParamStr
        : substr($callParamStr, 0, -2);

    /** ------------------- 根据@return对结果类型的简单断言 ------------------ **/
    $returnAssert = '';

    $docComment = $rMethod->getDocComment();
    $docCommentArr = explode("\n", $docComment);
    foreach ($docCommentArr as $comment) :
        if (strpos($comment, '@return') == false) :
            continue;
        endif;
        $returnCommentArr = explode(' ', strrchr($comment, '@return'));
        if (count($returnCommentArr) >= 2) :
            switch (strtolower($returnCommentArr[1])) :
                case 'bool':
                case 'boolean':
                    $returnAssert = '$this->assertTrue(is_bool($rs));';
                    break;
                case 'int':
                    $returnAssert = '$this->assertTrue(is_int($rs));';
                    break;
                case 'integer':
                    $returnAssert = '$this->assertTrue(is_integer($rs));';
                    break;
                case 'string':
                    $returnAssert = '$this->assertTrue(is_string($rs));';
                    break;
                case 'object':
                    $returnAssert = '$this->assertTrue(is_object($rs));';
                    break;
                case 'array':
                    $returnAssert = '$this->assertTrue(is_array($rs));';
                    break;
                case 'float':
                    $returnAssert = '$this->assertTrue(is_float($rs));';
                    break;
            endswitch;

            break;
        endif;
    endforeach;
?>
    /** ------------------- 基本的单元测试代码生成 ------------------ **/
    /**
     * @group test<?=$fun?>
     */ 
    public function test<?=$fun?>() {
        <?= empty($initParamStr) ? '' : $initParamStr ?>

        <?= $isStatic ? "\$rs = $className::$fun($callParamStr);" : "\$rs = \$this->$objName->$fun($callParamStr);"?>
        <?= empty($returnAssert) ? '' : $returnAssert?>
    }

    /** ------------------- 根据@testcase 生成测试代码 ------------------ **/
    <?php $caseNum = 0;
    foreach ($docCommentArr as $comment) :
        if (strpos($comment, '@testcase') == false) {
            continue;
        }

        $returnCommentArr = explode(' ', strrchr($comment, '@testcase'));
        if (count($returnCommentArr) > 1) :
            $expRs = $returnCommentArr[1];
            $callParamStrInCase = isset($returnCommentArr[2]) ? $returnCommentArr[2] : '';

           ?>
    /**
     * @group test$Fun
     */ 
    public function test<?=$Fun?>Case<?=$caseNum?>() {"

                <?= $isStatic ? "\$rs = $className::$fun($callParamStrInCase);" : "\$rs = \$this->$objName->$fun($callParamStrInCase);"?>
        $this->assertEquals(<?=$expRs?>, $rs);"
    }
<?php
            $caseNum ++;

        endif;

    endforeach;

endforeach;
?>
}
