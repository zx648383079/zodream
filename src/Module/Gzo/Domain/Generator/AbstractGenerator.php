<?php
namespace Zodream\Module\Gzo\Domain\Generator;

/**
 * Generator for skeletons.
 *
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2012-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.0.0
 */
abstract class AbstractGenerator {
    /**
     * @var array
     */
    protected $inClassName;

    /**
     * @var string
     */
    protected $inSourceFile;

    /**
     * @var array
     */
    protected $outClassName;

    /**
     * @var string
     */
    protected $outSourceFile;

    /**
     * Constructor.
     *
     * @param string $inClassName
     * @param string $inSourceFile
     * @param string $outClassName
     * @param string $outSourceFile
     */
    public function __construct($inClassName,
                                $inSourceFile = '',
                                $outClassName = '',
                                $outSourceFile = '') {
        $this->inClassName = $this->parseFullyQualifiedClassName(
            $inClassName
        );

        $this->outClassName = $this->parseFullyQualifiedClassName(
            $outClassName
        );

        $this->inSourceFile = str_replace(
            $this->inClassName['fullyQualifiedClassName'],
            $this->inClassName['className'],
            $inSourceFile
        );

        $this->outSourceFile = str_replace(
            $this->outClassName['fullyQualifiedClassName'],
            $this->outClassName['className'],
            $outSourceFile
        );
    }

    /**
     * @return string
     */
    public function getOutClassName() {
        return $this->outClassName['fullyQualifiedClassName'];
    }

    /**
     * @return string
     */
    public function getOutSourceFile() {
        return $this->outSourceFile;
    }

    /**
     * Generates the code and writes it to a source file.
     *
     * @param string $file
     */
    public function write($file = '') {
        if ($file == '') {
            $file = $this->outSourceFile;
        }

        file_put_contents($file, $this->generate());
    }

    /**
     * @param  string $className
     * @return array
     */
    protected function parseFullyQualifiedClassName($className) {
        $result = array(
            'namespace'               => '',
            'className'               => $className,
            'fullyQualifiedClassName' => $className
        );

        if (strpos($className, '\\') !== false) {
            $tmp                 = explode('\\', $className);
            $result['className'] = $tmp[count($tmp)-1];
            $result['namespace'] = $this->arrayToName($tmp);
        }

        return $result;
    }

    /**
     * @param  array $parts
     * @return string
     */
    protected function arrayToName(array $parts) {
        $result = '';

        if (count($parts) > 1) {
            array_pop($parts);

            $result = join('\\', $parts);
        }

        return $result;
    }

    /**
     * @return string
     */
    abstract public function generate();
}
