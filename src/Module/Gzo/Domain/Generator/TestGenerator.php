<?php
namespace Zodream\Module\Gzo\Domain\Generator;

use Zodream\Service\Factory;
/**
 * Generator for test class skeletons from classes.
 *
 * @author     Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright  2012-2014 Sebastian Bergmann <sebastian@phpunit.de>
 * @license    http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.0.0
 */
class TestGenerator extends AbstractGenerator {
    /**
     * @var array
     */
    protected $methodNameCounter = array();

    /**
     * Constructor.
     *
     * @param string $inClassName
     * @param string $inSourceFile
     * @param string $outClassName
     * @param string $outSourceFile
     * @throws \RuntimeException
     */
    public function __construct(
        $inClassName,
        $inSourceFile = '',
        $outClassName = '',
        $outSourceFile = '') {
        if (class_exists($inClassName)) {
            $reflector    = new \ReflectionClass($inClassName);
            $inSourceFile = $reflector->getFileName();

            if ($inSourceFile === false) {
                $inSourceFile = '<internal>';
            }

            unset($reflector);
        } else {
            if (empty($inSourceFile)) {
                $possibleFilenames = array(
                    $inClassName . '.php',
                    str_replace(
                        array('_', '\\'),
                        DIRECTORY_SEPARATOR,
                        $inClassName
                    ) . '.php'
                );

                foreach ($possibleFilenames as $possibleFilename) {
                    if (is_file($possibleFilename)) {
                        $inSourceFile = $possibleFilename;
                    }
                }
            }

            if (empty($inSourceFile)) {
                throw new \RuntimeException(
                    sprintf(
                        'Neither "%s" nor "%s" could be opened.',
                        $possibleFilenames[0],
                        $possibleFilenames[1]
                    )
                );
            }

            if (!is_file($inSourceFile)) {
                throw new \RuntimeException(
                    sprintf(
                        '"%s" could not be opened.',
                        $inSourceFile
                    )
                );
            }

            $inSourceFile = realpath($inSourceFile);
            include_once $inSourceFile;

            if (!class_exists($inClassName)) {
                throw new \RuntimeException(
                    sprintf(
                        'Could not find class "%s" in "%s".',
                        $inClassName,
                        $inSourceFile
                    )
                );
            }
        }

        if (empty($outClassName)) {
            $outClassName = $inClassName . 'Test';
        }

        if (empty($outSourceFile)) {
            $outSourceFile = dirname($inSourceFile) . DIRECTORY_SEPARATOR . $outClassName . '.php';
        }

        parent::__construct(
            $inClassName,
            $inSourceFile,
            $outClassName,
            $outSourceFile
        );
    }

    /**
     * @return string
     */
    public function generate()
    {
        $class = new \ReflectionClass(
            $this->inClassName['fullyQualifiedClassName']
        );

        $methods           = '';
        $incompleteMethods = '';

        foreach ($class->getMethods() as $method) {
            if (!$method->isConstructor() &&
                !$method->isAbstract() &&
                $method->isPublic() &&
                $method->getDeclaringClass()->getName() == $this->inClassName['fullyQualifiedClassName']) {
                $assertAnnotationFound = false;

                if (preg_match_all('/@assert(.*)$/Um', $method->getDocComment(), $annotations)) {
                    foreach ($annotations[1] as $annotation) {
                        if (preg_match('/\((.*)\)\s+([^\s]*)\s+(.*)/', $annotation, $matches)) {
                            switch ($matches[2]) {
                                case '==':
                                    $assertion = 'Equals';
                                    break;

                                case '!=':
                                    $assertion = 'NotEquals';
                                    break;

                                case '===':
                                    $assertion = 'Same';
                                    break;

                                case '!==':
                                    $assertion = 'NotSame';
                                    break;

                                case '>':
                                    $assertion = 'GreaterThan';
                                    break;

                                case '>=':
                                    $assertion = 'GreaterThanOrEqual';
                                    break;

                                case '<':
                                    $assertion = 'LessThan';
                                    break;

                                case '<=':
                                    $assertion = 'LessThanOrEqual';
                                    break;

                                case 'throws':
                                    $assertion = 'exception';
                                    break;

                                default:
                                    throw new \RuntimeException(
                                        sprintf(
                                            'Token "%s" could not be parsed in @assert annotation.',
                                            $matches[2]
                                        )
                                    );
                            }

                            if ($assertion == 'exception') {
                                $template = 'TestMethodException';
                            } elseif ($assertion == 'Equals' && strtolower($matches[3]) == 'true') {
                                $assertion = 'True';
                                $template  = 'TestMethodBool';
                            } elseif ($assertion == 'NotEquals' && strtolower($matches[3]) == 'true') {
                                $assertion = 'False';
                                $template  = 'TestMethodBool';
                            } elseif ($assertion == 'Equals' && strtolower($matches[3]) == 'false') {
                                $assertion = 'False';
                                $template  = 'TestMethodBool';
                            } elseif ($assertion == 'NotEquals' && strtolower($matches[3]) == 'false') {
                                $assertion = 'True';
                                $template  = 'TestMethodBool';
                            } else {
                                $template = 'TestMethod';
                            }

                            if ($method->isStatic()) {
                                $template .= 'Static';
                            }

                            $origMethodName = $method->getName();
                            $methodName     = ucfirst($origMethodName);

                            if (isset($this->methodNameCounter[$methodName])) {
                                $this->methodNameCounter[$methodName]++;
                            } else {
                                $this->methodNameCounter[$methodName] = 1;
                            }

                            if ($this->methodNameCounter[$methodName] > 1) {
                                $methodName .= $this->methodNameCounter[$methodName];
                            }

                            $methods .= Factory::view()
                                ->render('Test.'.$template, array(
                                    'annotation'     => trim($annotation),
                                    'arguments'      => $matches[1],
                                    'assertion'      => isset($assertion) ? $assertion : '',
                                    'expected'       => $matches[3],
                                    'origMethodName' => $origMethodName,
                                    'className'      => $this->inClassName['fullyQualifiedClassName'],
                                    'methodName'     => $methodName
                                ));;

                            $assertAnnotationFound = true;
                        }
                    }
                }

                if (!$assertAnnotationFound) {

                    $incompleteMethods .= Factory::view()
                        ->render('Test.IncompleteTestMethod', array(
                            'className'      => $this->inClassName['fullyQualifiedClassName'],
                            'methodName'     => ucfirst($method->getName()),
                            'origMethodName' => $method->getName()
                        ));
                }
            }
        }

        if ($this->outClassName['namespace'] != '') {
            $namespace = "\nnamespace " .
                $this->outClassName['namespace'] . ";\n";
        } else {
            $namespace = '';
        }

        return Factory::view()
            ->render('Test.TestClass', array(
                'namespace'          => $namespace,
                'namespaceSeparator' => !empty($namespace) ? '\\' : '',
                'className'          => $this->inClassName['className'],
                'testClassName'      => $this->outClassName['className'],
                'methods'            => $methods . $incompleteMethods,
            ));
    }
}
