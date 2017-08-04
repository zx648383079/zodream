<?php
$help = <<<'HELP'
EXAMPLES
   ./project-phpunit-skelgen.php -s <dir> -o <dir> -b <file>
DESCRIPTION
    Recursively find PHP files and run phpunit-skelgen to create unit test skeleton files.
    Files must use the .php extension and be PSR-0 compliant, contain a namespace and class name.
    Files ending in 'Interface.php' are ignored.
OPTIONS
    -s <dir>, --source <dir>
    Source directory to scan for PHP files
    -t <dir>, --target <dir>
    Target for the generated UnitTest files. Default is current-working-dir/tests.
    -b <file>, --bootstrap <file>
    Specify a bootstrap file to be used by phpunit-skelgen for loading the classes.
    -v, --verbose
    Switch on verbose output
    -h, --help
    Prints this help
HELP;
$options = getopt('s:t::hb:v', array(
    'source:',
    'target::',
    'bootstrap:',
    'help',
    'verbose',
));
if (empty($options) || !is_null(optval($options, 'h', 'help'))) {
    echo "$help\n";
    return;
}
// Extract the inputs
$source         = optval($options, 's', 'source');
$target         = optval($options, 't', 'target');
$bootstrap      = optval($options, 'b', 'bootstrap');
$verbose        = !is_null(optval($options, 'v', 'verbose'));
// Validate inputs
if (empty($source) || !is_dir($source)) {
    echo "Source given '$source' is not a directory\n";
}
if (empty($target)) {
    $target = './tests';
}
if (!is_dir($target)) {
    mkdir($target, 0777, true);
}
$sourceDir      = realpath($source);
$targetDir      = realpath($target);
// $binary         = 'phpunit-skelgen.phar';
// $skelgenBinary  = trim(shell_exec(strtoupper(substr(PHP_OS,0,3))==='WIN' ? "where $binary" : "which $binary"));
// if (!file_exists($skelgenBinary)) {
//     echo "Unable to locate $binary\n";
// }
// Scan and process the source directory
$dirIterator    = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir)
);
$sourcePathStrLength    = strlen($sourceDir);
if (!$bootstrap) {
    $bootstrap = $targetDir.'/bootstrap.php';
}
$escapedBootstrap       = $bootstrap ? '--bootstrap='.escapeshellarg("$bootstrap") : '';
foreach ($dirIterator as $filePath => $fileInfo) {
    // @var SplFileInfo $fileInfo
    if (
        $fileInfo->isDir() ||
        substr($filePath, -13) === 'Interface.php'
    ) {
        continue;
    }
    $targetFilePath     = $targetDir . preg_replace(
        '/\.php$/i',
        'Test.php',
        substr($filePath, $sourcePathStrLength)
    );
    if (file_exists($targetFilePath)) {
        echo "Skip:     Test file for '$filePath' already exists\n";
        continue;
    }
    $fullClassName      = extractFullClassNameFromFile($filePath);
    if ($fullClassName === false) {
        echo "Err:      Class name could not be extracted from '$filePath'\n";
        continue;
    }
    $targetFileName     = basename($targetFilePath);
    $sourceDirPath      = dirname($filePath);
    $targetDirPath      = dirname($targetFilePath);
    $generatedFilePath  = "$sourceDirPath/$targetFileName";
    if (!is_dir($targetDirPath)) {
        mkdir($targetDirPath, 0777, true);
    }
    $escapedClassName   = escapeshellarg($fullClassName);
    $escapedSourcePath  = escapeshellarg($filePath);
    $result             = shell_exec(
        "phpunit-skelgen --ansi generate-test $escapedBootstrap $escapedClassName $escapedSourcePath {$escapedClassName}Test $generatedFilePath"
    );
    if ($verbose) {
        echo $result;
    }
    if (!file_exists($generatedFilePath)) {
        echo "Error:    Failed to generate test file for '$fullClassName'\n";
        continue;
    }
    rename($generatedFilePath, $targetFilePath);
    echo "OK:       Test file successfully created for '$fullClassName'\n";
}
echo "DONE.\n";
/* ---- FUNCTIONS ---- */
/**
 * Extract an option from the results of getopt specifying the
 * keys in order of preference.
 *
 * @param   array   $options    Result from getopt('ab:c::', array('add', 'bacon', 'create'))
 * @param   string  $key1       First key to look for e.g. 'add'
 * @param   string  $key2       Second key to look for e.g. 'a'
 * @param   string  $keyn       Other possible aliases...
 * @return  mixed               The value from options array or null otherwise
 */
function optval(array &$options, $key1, $key2, $keyn = null)
{
    $args       = func_get_args();
    $options    = array_shift($args);
    foreach ($args as $key) {
        if (isset($options[$key])) {
            return $options[$key];
        }
    }
    return null;
}
/**
 * Conert to boolean by using normal PHP falsey equivalencies with
 * the addition of string 'false' which PHP usually considers truthy.
 *
 * @param   mixed   $val        Value to convert to boolean
 * @return  boolean             Boolean equivalent
 */
function bool($val)
{
    if (
        $val === '' ||
        $val === 'false' ||
        $val === '0' ||
        $val === 0 ||
        $val === null ||
        $val === array() ||
        $val === 0.0 ||
        $val === false
    ) {
        return false;
    }
    return true;
}
/**
 * Extract a fully qualfied (namespaced) classname from a php file.
 * This function assumes PSR-0 compliance.
 *
 * @param   string  $filePath   Path to the php file.
 * @return  string|false        The resulting classname
 */
function extractFullClassNameFromFile($filePath)
{
    if (!file_exists($filePath)) {
        return false;
    }
    $namespace  = null;
    $classname  = null;
    $cnMatches  = null;
    $nsMatches  = null;
    $file       = file_get_contents($filePath);
    if (preg_match_all('/\n\s*(abstract\s|final\s)*class\s+(?<name>[^\s;]+)\s*/i', $file, $cnMatches, PREG_PATTERN_ORDER)) {
        $classname  = array_pop($cnMatches['name']);
        if (preg_match_all('/namespace\s+(?<name>[^\s;]+)\s*;/i', $file, $nsMatches, PREG_PATTERN_ORDER)) {
            $namespace  = array_pop($nsMatches['name']);
        }
    }
    if (empty($classname)) {
        return false;
    }
    return "$namespace\\$classname";
}