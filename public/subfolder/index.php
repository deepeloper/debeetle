<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

use deepeloper\Debeetle\d;
use deepeloper\Debeetle\Loader;

/**
 * @todo Find out all "///" & "###".
 */

error_reporting(E_ALL);
//ini_set("display_errors", true);

/**
 * Place this struct definition to the every script entry point you will debug.
 *
 * See "Debeetle initialization" section.
 */
$scriptInitState = [
    'serverTime' => isset($_SERVER['REQUEST_TIME']) ? $_SERVER['REQUEST_TIME'] : time(),
    'time' => isset($_SERVER['REQUEST_TIME_FLOAT']) ? $_SERVER['REQUEST_TIME_FLOAT'] : microtime(true),
    'memoryUsage' => memory_get_usage(),
    'peakMemoryUsage' => function_exists('memory_get_peak_usage') ? memory_get_peak_usage() : null,
    'entryPoint' => [
        'file' => __FILE__,
        'line' => __LINE__,
    ],
];

$debeetleInitState = [
    'time' => microtime(true),
    'memoryUsage' => memory_get_usage(),
    'peakMemoryUsage' => function_exists('memory_get_peak_usage') ? memory_get_peak_usage() : null,
    'includedFiles'   => sizeof(get_included_files()),
    'entryPoint' => [
        'file' => __FILE__,
        'line' => __LINE__,
    ],
];

$debeetlePath = realpath("./../..");
require_once "$debeetlePath/vendor/autoload.php";

try {
    Loader::startup([
        'config' => realpath("$debeetlePath/config.xml.php"),
//        'config' => realpath("$debeetlePath/config.json.php"),
        'developerMode' => true, // To see startup errors.
        'scriptInitState' => $scriptInitState,
        'initState' => $debeetleInitState,
    ]);
    // Add example locales.
    /**
     * @var deepeloper\Debeetle\DebeetleInterface $debeetle
     */
    $debeetle = d::getInstance();
    if ($debeetle) {
        $settings = $debeetle->getSettings();
        foreach (array_unique([$settings['defaults']['language'], "en"]) as $language) {
            $path = sprintf("%s/locales/%s.php",__DIR__, $language);
            if (file_exists($path)) {
                $debeetle->getView()->addLocales(require $path);
                break;
            }
        }
    }
    unset($debeetle, $settings, $path, $language);
} catch (Exception $debeetleException) { // @todo Replace with Throwable for PHP >= 7.
}
unset($debeetlePath, $scriptInitState, $debeetleInitState);

/*echo '<pre>';
echo 'time diff: '; var_dump(microtime(true) - $scriptInitState['time']);
echo 'included files diff: '; var_dump(sizeof(get_included_files()) - $debeetleInitState['includedFiles']);
echo 'mem usage diff: '; var_dump(memory_get_usage() - $scriptInitState['memoryUsage']);
echo 'peak mem usage diff: '; var_dump(memory_get_peak_usage() - $scriptInitState['peakMemoryUsage']);
echo '</pre>';*/

// } Debeetle initialization

function highlight($code)
{
    d::w(highlight_string("<?php $code", true), ['htmlEntities' => false]);
}

d::t("examples|common");

highlight('d::du(null);');
d::du(null);

highlight('d::du(false);');
d::du(false);

highlight('d::du(1);');
d::du(1);

highlight('d::du(1.34);');
d::du(1.34);

highlight('d::w("&raquo;&raquo;\n", [\'htmlEntities\' => false]);');
d::w("&raquo;&raquo;\n", ['htmlEntities' => false]);

highlight('d::du([1, 2, 3]);');
d::du([1, 2, 3]);

highlight('d::du("\'", \'quote\');');
d::du("'", 'quote');

highlight('d::du(\'"\', \'double quote\');');
d::du('"', 'double quote');

highlight('d::du("Long Long Long...", "", [\'maxStringLength\' => 30]);');
d::du(
    "Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long" .
    "Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long " .
    "Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long " .
    "Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long Long",
    "",
    ['maxStringLength' => 30]
);

$code = <<<EOT


\$fh = fopen(\$_SERVER['SCRIPT_FILENAME'], "r");
d::du(\$fh);
EOT;
highlight($code);
eval("use deepeloper\Debeetle\d; $code");

$code = <<<EOT


class Foo
{
    public \$x = 1;
    public \$nestingEntities = [1, 2, ['key' => 1]];
    public \$publicNull;
    protected \$protectedNull;
    private \$privateNull;

    public function __construct()
    {
        \$this->traceCaller(\$this);
    }

    public function traceCaller(\$object)
    {
        d::trace();
    }
}
\$foo = new Foo;
\$foo->nestingEntities[] = \$foo;
EOT;

highlight($code);
eval("use deepeloper\Debeetle\d; $code");

highlight('d::du($foo, \'Foo!\');');
/**
 * @var Foo $foo
 */
d::du($foo, 'Foo!');

d::t("examples|backslashedTabName");

highlight('d::w("Single backslash \\\\");');
d::w("Single backslash \\");


$code = <<<EOT


d::t("examples|nestedTabs");
d::t("examples|nestedTabs|level3");
d::t("examples|nestedTabs|level3|level4");
d::t("examples|nestedTabs|level3|level4|level5");
EOT;
eval("use deepeloper\Debeetle\d; $code");
highlight($code);

session_start();

d::t("environment");
d::t("environment|get");
d::dump($_GET);
d::t("environment|post");
d::dump($_POST);
d::t("environment|cooklie");
d::dump($_COOKIE);
d::t("environment|request");
d::dump($_REQUEST);
d::t("environment|session");
d::dump($_SESSION);
d::t("environment|server");
d::dump($_SERVER);

trigger_error("User notice");
trigger_error("User warning", E_USER_WARNING);
trigger_error("User deprecated", E_USER_DEPRECATED);
trigger_error("User error", E_USER_ERROR);
$undefined = []; $undefined['undefined']; // NOTICE
$a = 10; foreach ($a as $b) {}// WARNING

//if (false === strpos($_SERVER['REQUEST_URI'], "?1")) {
//  header("Location: http://deepeloper.home/subfolder/?1");
//  die;
//}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Debeetle panel demo</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body style="color: #333; background: #aef;">
<div>
<p>
Aliquam commodo eros quis diam volutpat pretium. Aenean tincidunt cursus lectus vel accumsan. Mauris porttitor malesuada nisl iaculis eleifend. Donec tincidunt ultrices pharetra. Morbi risus arcu, varius eget accumsan at, adipiscing id velit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nullam mi elit, suscipit non adipiscing vitae, aliquet feugiat libero. Pellentesque ut commodo mi. Proin ullamcorper lobortis bibendum. Nam nec ante orci. In luctus dui urna, nec interdum erat. Maecenas quis massa sapien. Aenean et est ut tortor posuere sagittis ut sed felis. Nam vestibulum ante vel dui faucibus porta. Nunc nisl tortor, rutrum vel luctus et, euismod et felis. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Mauris convallis libero et urna consequat aliquet. Mauris vel ipsum dolor, et tincidunt metus.
</p>
<p>
Donec pretium gravida fermentum. Quisque ut elementum ipsum. Curabitur tincidunt, enim vitae pharetra hendrerit, justo mi dignissim urna, sit amet ultrices mi massa in urna. Sed luctus quam eu libero semper quis fermentum diam elementum. Pellentesque ac auctor justo. In fermentum metus ut leo pharetra lobortis. Maecenas adipiscing tortor a quam cursus non bibendum lacus consequat. Nunc id mauris quis sem rhoncus molestie. Sed nec tempus augue. Aenean mi quam, mattis vel auctor sed, pulvinar id massa. Integer gravida venenatis augue, ac elementum ipsum elementum sagittis. Fusce non lorem magna. Etiam sit amet augue posuere nulla lacinia malesuada. Morbi eu est purus. Cras semper, dolor vitae varius faucibus, ligula magna scelerisque dolor, quis vestibulum purus diam sed diam. Vivamus malesuada dui at nibh commodo facilisis. Donec convallis fermentum nulla at tincidunt.
</p>
<p>
Phasellus accumsan, urna vel adipiscing interdum, augue eros consectetur justo, sit amet pharetra lorem turpis sed nulla. In ornare purus ac augue venenatis ultricies. Vestibulum in risus a sapien faucibus convallis. Ut eget odio ligula, facilisis tempus nisl. Duis non augue ut tellus volutpat rhoncus vitae eget metus. Nullam vel nisi ipsum. Etiam convallis, nisi quis tincidunt ornare, arcu odio venenatis dolor, vitae scelerisque orci mauris in risus. Nulla sed tortor erat, vel facilisis metus. Vivamus volutpat sapien sed mi imperdiet venenatis. In id tortor ac ipsum congue laoreet. Pellentesque molestie bibendum placerat. Etiam vitae massa magna. Aenean in enim vitae felis pulvinar eleifend. Nam tempus aliquam dui, id convallis urna feugiat at. Praesent ultricies, ligula sit amet accumsan dignissim, metus sem hendrerit tortor, a vehicula nunc nibh consectetur felis. Sed dignissim scelerisque mauris, et lobortis ligula cursus nec. Vivamus mi arcu, mollis sit amet molestie at, aliquam vitae quam.
</p>
<p>
Mauris id leo sapien, sed viverra mi. Nulla facilisi. Quisque egestas aliquam rutrum. Morbi ultricies vestibulum pulvinar. Cras pretium dui quis dolor ornare a porttitor lacus fringilla. Cras non sapien non metus suscipit fermentum quis in enim. Etiam facilisis pharetra adipiscing. Quisque aliquet imperdiet est. Curabitur metus quam, lacinia eu imperdiet nec, aliquam id urna. Nunc pretium feugiat est ut faucibus. Praesent sed ipsum eu orci rutrum facilisis nec id lorem. Morbi vel sollicitudin sapien. Etiam quis nibh ligula. Cras ac ullamcorper diam.
</p>

<span style="background: #8c8;">
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />
Suspendisse interdum dictum est, sed laoreet ante dapibus nec. Nullam turpis nisl, sollicitudin sed euismod id, consectetur quis orci. Pellentesque rhoncus libero justo. In hac habitasse platea dictumst. Fusce tincidunt tristique lectus, a hendrerit ligula aliquam vel. Nullam non leo erat, et pellentesque magna. In nec ipsum augue. Pellentesque aliquam mauris ut augue vehicula ultrices eu ut lectus. Suspendisse venenatis lectus tincidunt justo tincidunt tincidunt. Nam volutpat aliquam vestibulum. Vestibulum quis ante nunc, vitae blandit neque. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Suspendisse aliquet malesuada nulla. Etiam viverra arcu eu tellus tristique vulputate. Mauris eu lectus eget enim rhoncus egestas. Duis ornare congue nulla.<br />

</span>

<p>

Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Ut purus ante, adipiscing at porta at, consectetur a quam. Morbi et interdum libero. In euismod, felis eleifend varius lobortis, purus orci posuere justo, luctus rutrum neque lectus nec libero. Mauris bibendum dictum ipsum, ut fermentum nunc sagittis et. Donec id sem ac tortor lacinia dapibus. Proin ut porttitor nibh. Ut nec purus et justo rutrum cursus at id tellus. Nulla lorem orci, laoreet vitae accumsan ac, sollicitudin eget lacus. In et dolor est. Phasellus ligula ipsum, molestie eget tincidunt vel, aliquam sed eros. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus mattis, quam vel pulvinar consectetur, arcu lacus ullamcorper elit, et blandit orci magna ultrices quam. Ut rhoncus leo sed quam tincidunt pharetra at id augue. Phasellus convallis dolor sed diam gravida quis lacinia quam consectetur. Proin porta, felis sit amet malesuada ornare, mauris sapien tincidunt elit, ut imperdiet nisl mi quis lacus.
</p>
<p>
Aliquam erat volutpat. Donec eu mi a mi placerat aliquet a nec neque. Cras volutpat accumsan sagittis. Vivamus a dolor sapien. Duis commodo viverra massa eu feugiat. Morbi consequat, nulla eu facilisis interdum, nisl ipsum condimentum elit, eget euismod quam nunc pretium ligula. Cras eu dolor suscipit dui aliquam gravida eu sed felis. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nunc sed purus vitae ipsum consequat dictum. Quisque nec magna sit amet sapien facilisis condimentum in ut nibh. Donec viverra lorem arcu. Praesent a est ac mi convallis ultrices ac vitae quam. Nulla at risus tellus. Morbi eu mauris nec sapien laoreet adipiscing ut ullamcorper nunc. Nam posuere sodales erat, id accumsan augue convallis sed.
</p>
<p>
Nullam felis mauris, ornare nec rhoncus sit amet, placerat placerat arcu. Donec non viverra lectus. Etiam convallis nulla vitae elit dictum at euismod augue semper. Donec blandit adipiscing turpis ut ornare. Phasellus nec est sed purus vulputate semper. Fusce accumsan lacinia dignissim. Aliquam tristique ornare leo, id scelerisque libero semper in. Mauris eu massa a lacus mollis congue quis vel lacus. Proin id velit tellus. Sed mollis aliquam tincidunt. Aenean ac nisl tortor, porttitor ullamcorper sapien. Suspendisse potenti. Integer interdum, diam non vehicula pulvinar, metus magna lacinia tellus, sit amet cursus lectus nisi eget turpis. Curabitur sollicitudin, nulla in commodo aliquet, elit orci luctus purus, eget interdum tellus risus ac sapien. Cras eleifend neque vitae mauris congue in imperdiet urna mollis. Nam lacinia tellus et lacus aliquam faucibus. Curabitur euismod nulla quis risus mollis in fringilla ipsum adipiscing. Integer ut ligula elit, nec sollicitudin nisi. Quisque fringilla odio eu justo congue accumsan. Quisque tempus adipiscing elit et vehicula.
</p>
<p>
Sed eu quam dolor, quis lobortis arcu. Curabitur vitae eros dui. Aenean molestie interdum ligula dapibus ullamcorper. Aenean venenatis mollis lacus in ultrices. Suspendisse neque diam, facilisis eu dapibus id, elementum in diam. Vivamus erat arcu, suscipit eget suscipit nec, elementum et diam. Donec malesuada convallis risus non condimentum. Suspendisse ac orci elit. Quisque nulla tortor, interdum euismod bibendum vel, venenatis sit amet enim. Vivamus accumsan pharetra ante, ac faucibus risus dignissim eu.
</p>
<p>
Nullam dapibus ornare libero vitae vulputate. Suspendisse potenti. Duis elementum, urna sit amet blandit vulputate, dui dolor laoreet ligula, convallis gravida nulla nibh quis tortor. Maecenas a gravida mauris. Curabitur bibendum velit et tellus blandit ac ornare velit mollis. Donec sed purus turpis. Nullam rutrum lorem vitae ligula convallis iaculis. In vitae hendrerit nunc. Mauris id commodo enim. Aliquam convallis, risus eu suscipit ultrices, tellus est rhoncus lacus, id adipiscing risus eros ac ante. Sed luctus mi in ante lacinia imperdiet. Suspendisse et purus diam, at malesuada neque.
</p>
<p>
In hac habitasse platea dictumst. Integer aliquam mollis erat sit amet congue. Etiam a cursus sem. Vestibulum imperdiet justo sit amet augue egestas id semper nisl fermentum. Vivamus sit amet augue id enim blandit porttitor quis sit amet orci. Donec molestie interdum nisi ut posuere. Nam purus purus, fringilla eu ultricies eget, tincidunt vitae tortor. Mauris in purus eu risus elementum egestas. Quisque ut augue turpis. Etiam laoreet pellentesque sem. Aliquam ultricies dui a mauris aliquam faucibus. Nulla nisi risus, sagittis in sollicitudin vitae, placerat non diam. In condimentum, justo et mollis tristique, lectus eros aliquam est, vel varius diam sem at quam. Praesent lorem nulla, porta id pulvinar non, porttitor sit amet lectus. Vestibulum viverra laoreet dictum. Nullam at mauris quis turpis consectetur condimentum. Morbi fermentum accumsan leo, non feugiat justo aliquam quis. Mauris blandit, felis et iaculis elementum, eros augue commodo enim, non condimentum nisi justo et metus. Cras mattis suscipit vestibulum. Vivamus vitae est quis orci vestibulum lacinia.
</p>

<p>
Donec non velit nec dolor dignissim consectetur. Morbi sagittis euismod leo, nec commodo velit molestie eget. Pellentesque eget molestie nisl. Nam porta tempor dui, eget sagittis arcu eleifend non. Mauris iaculis placerat egestas. Phasellus id consectetur mauris. Sed vel ornare nunc. Pellentesque nec nisi nibh, ut pulvinar felis. Integer in velit non turpis consectetur posuere. Pellentesque quis nulla ut elit pulvinar aliquet in a nulla. Curabitur eget pharetra nisl. Sed hendrerit velit at ante facilisis et convallis lectus fermentum. Donec a neque enim, non gravida dui. Aenean risus erat, ullamcorper eget fringilla at, pretium sed mauris. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nulla non quam dui. Suspendisse felis arcu, auctor non varius sed, sodales eu turpis. Nullam elementum ligula lacinia magna placerat vitae eleifend ligula cursus. Vestibulum vitae massa vitae neque dapibus accumsan. Nulla et semper nunc.
</p>
<p>
Donec vitae elit urna, non egestas erat. Quisque in nibh sem. Maecenas dolor quam, vestibulum eget venenatis in, gravida ut est. Donec tempor, nibh ut euismod viverra, libero risus mollis lacus, vel faucibus odio tellus consequat augue. Phasellus aliquet sodales tortor nec bibendum. Duis et neque magna. Vivamus leo sapien, tristique at hendrerit nec, pellentesque quis lacus. Nullam congue rhoncus bibendum. Duis hendrerit, purus in congue scelerisque, tortor sapien scelerisque lectus, ac ultrices sapien diam id orci. Phasellus dictum vehicula ipsum, mattis aliquet nisl dapibus ut. Vivamus et felis ante. Cras lobortis ipsum ut velit placerat rhoncus. Nam nec lectus tempus tellus congue congue. Quisque in risus tortor. Cras at nunc ligula. Cras sed gravida velit. Mauris eu nibh sit amet risus lobortis eleifend at sed lacus. Cras eros erat, eleifend rutrum luctus sit amet, hendrerit vel mauris.
</p>
<p>
Donec ornare faucibus nibh, at tincidunt metus malesuada non. Proin sed leo purus, quis semper orci. Quisque non lorem sit amet tortor mollis facilisis eu non quam. Proin vulputate mauris sed felis vehicula eget ornare justo aliquam. Sed at metus sed tellus posuere ullamcorper. Vivamus placerat egestas arcu eget aliquam. Nullam non sapien vel diam tempor mollis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed neque mauris, convallis molestie semper a, tempus ut nibh. Sed congue scelerisque ipsum, eu malesuada eros blandit nec. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec semper diam vel sem volutpat quis pretium nibh aliquet. Nunc a justo et nunc ullamcorper pellentesque quis id leo. Mauris felis erat, facilisis at condimentum eget, viverra eget mauris. Cras quis dui at ligula porttitor congue vitae vitae arcu. Praesent purus justo, laoreet sit amet ornare vitae, ornare sed sem. Donec scelerisque lacinia lacus sollicitudin tristique. Etiam nibh ante, porttitor ut euismod nec, varius in dui. Praesent eget elit at urna imperdiet pretium. Maecenas tristique mi ut diam bibendum venenatis.
</p>
<p>
Duis viverra dui eu odio sodales a placerat augue pulvinar. Aliquam erat volutpat. Curabitur tempus odio mauris. Nulla facilisi. Sed facilisis, mauris vel hendrerit dignissim, lectus magna placerat risus, nec bibendum enim lectus quis diam. Aenean vitae sapien est, quis cursus nulla. Sed magna diam, euismod ut iaculis nec, fermentum dictum nisi. Maecenas laoreet, mi et semper ornare, metus dolor ullamcorper dolor, at hendrerit magna dui vitae lorem. Sed fermentum malesuada luctus. In hac habitasse platea dictumst. Donec dolor velit, suscipit nec aliquet sed, pretium luctus sapien. Duis convallis blandit neque at commodo. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Maecenas lectus est, aliquam vel condimentum interdum, varius in nisl. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.
</p>
<p>
Suspendisse vitae urna vel velit mattis mollis ut non mauris. Donec id diam tortor. Ut pellentesque venenatis varius. Cras et libero vel dolor molestie varius. Donec dignissim sollicitudin velit, non viverra est mattis sit amet. Ut dolor enim, dictum vel ullamcorper nec, venenatis vitae nisl. Phasellus ac ligula fringilla odio mattis posuere. Fusce nulla massa, tempor in faucibus et, molestie vitae urna. In sodales, elit vitae euismod congue, arcu erat consectetur nulla, ac vulputate urna turpis eget turpis. Etiam dolor massa, laoreet vitae eleifend tincidunt, scelerisque at tortor. Aliquam lobortis turpis sed metus pretium posuere. Nullam tellus sem, euismod ut cursus quis, aliquam eu neque. Aliquam convallis ipsum ac erat varius fringilla sit amet nec sapien. Curabitur vehicula dolor vitae sapien gravida dapibus.
</p>
<p>
Vivamus tortor sem, imperdiet congue sollicitudin non, consequat sit amet diam. Phasellus non quam nec nisi interdum auctor mollis at nisi. Maecenas vel est quis mauris egestas laoreet. Maecenas eu turpis vel lectus ullamcorper dapibus. Praesent a tortor diam. Etiam at tellus nec ante vestibulum fringilla ut id ipsum. Nunc ac orci sapien, ut pretium dui. In sit amet justo lacus, non laoreet diam. Etiam a ipsum dui. Quisque eu felis eget nunc lacinia scelerisque.

</p>
<p>
In hac habitasse platea dictumst. Morbi luctus luctus volutpat. Nulla porta, turpis eget fermentum egestas, mauris tellus pulvinar libero, id aliquam dui nunc id quam. Cras vel ligula sed augue egestas vestibulum imperdiet at nisi. Vestibulum luctus turpis eu quam condimentum at lobortis neque tincidunt. Sed et risus quis ante condimentum molestie. Maecenas fringilla lorem eget erat sodales mollis. Quisque eu faucibus tellus. Ut vulputate, libero non sollicitudin commodo, mi odio tincidunt est, eu dignissim lacus felis eget elit. Donec lobortis ipsum nec dui lobortis at accumsan purus elementum. Donec scelerisque elit non tellus tristique sodales. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In pulvinar rhoncus commodo. Nunc in sollicitudin elit. Donec tempus, metus mollis placerat ultricies, magna lectus volutpat dui, ut rhoncus ante turpis vel augue. Aenean iaculis libero rhoncus orci pellentesque volutpat. Donec eu nulla ultrices purus porta tincidunt in in ipsum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.
</p>
<p>
Sed nisl tortor, porttitor eu posuere et, adipiscing non nunc. Pellentesque aliquam, enim et eleifend placerat, quam elit vulputate sapien, adipiscing hendrerit eros libero quis dolor. Donec porttitor dui et ligula scelerisque posuere. Nam aliquet ornare urna, sit amet suscipit mauris interdum et. Curabitur tortor eros, commodo id ultrices a, tristique ac justo. Suspendisse non dignissim nisi. Aliquam nisi libero, molestie eu pellentesque quis, rutrum ac urna. In placerat tellus at magna pellentesque a ornare elit aliquet. Ut eget lorem elit, ac pellentesque lacus. Ut posuere interdum dolor, ac semper nisl tempus at. Nulla urna risus, lacinia sit amet accumsan eu, ullamcorper luctus leo. Aenean non ligula lorem. Aliquam et sapien vitae velit rutrum faucibus nec a tortor. Morbi et velit ac nunc hendrerit imperdiet nec non augue. Vestibulum fermentum tempor dui, id blandit lorem rutrum et. Nullam auctor dignissim vestibulum. Pellentesque auctor consequat purus non tincidunt. Sed imperdiet sagittis odio vel pharetra. Suspendisse vehicula ipsum odio, ut adipiscing urna.
</p>
<p>
Donec ac sapien eu nunc malesuada egestas. Nulla ultricies, elit vitae varius rutrum, mi nulla dignissim nisl, et luctus leo nibh in sapien. Aliquam at sem bibendum elit convallis vehicula in in risus. Cras sit amet tellus quis magna lobortis dignissim. Maecenas aliquam rutrum magna at commodo. Ut metus sapien, vestibulum sed vehicula sed, convallis ut nisl. Aenean nec tincidunt est. Curabitur rutrum, quam et iaculis venenatis, ligula turpis lacinia ipsum, et volutpat nibh neque vitae lectus. Aliquam sit amet urna vitae massa tempus cursus. Donec consequat fringilla tincidunt. Suspendisse id justo odio, ac suscipit dui. Nulla facilisi. Praesent ac tortor eget quam pulvinar condimentum id sed nisl. Suspendisse eu enim vitae libero tristique scelerisque sed a tortor. Praesent imperdiet pretium gravida. Fusce aliquet mattis quam, vitae tristique nibh volutpat at. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nam aliquet, turpis ullamcorper lobortis facilisis, augue nulla euismod purus, sit amet molestie ante tortor nec leo. Nullam blandit quam non elit vulputate non euismod nibh laoreet.
</p>
<p>
In et tellus id sapien ullamcorper pretium ac eleifend nulla. Suspendisse potenti. Etiam ut iaculis dolor. Maecenas at nunc ac nisl semper viverra. In ac justo dui, eu pharetra turpis. Donec ornare lorem ut diam laoreet euismod. Fusce tortor erat, mollis in scelerisque non, consectetur at nisi. Integer quis lorem magna, vulputate faucibus lorem. Aenean auctor congue metus, eget mattis nisl tempor quis. In tempus tempus quam vitae sodales. Nunc malesuada, risus ut ultricies ullamcorper, enim mi tempor lectus, in vestibulum eros diam ut libero. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aenean convallis eros sed odio dignissim scelerisque. Nullam feugiat consequat volutpat. In placerat tellus id arcu cursus dictum.
</p>
<p>
Donec sit amet leo ac metus iaculis ullamcorper. Praesent porta tortor sit amet enim elementum vestibulum. Sed eget libero libero, nec luctus metus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi ac ante mi, at bibendum purus. Nunc commodo, metus eget molestie dapibus, nisl dolor bibendum orci, ac commodo felis ligula et lacus. Integer eget dolor eget ligula sollicitudin mollis. Fusce sit amet elit in urna faucibus rhoncus iaculis vulputate erat. In quis diam a mi viverra dictum. Vestibulum at elit vitae eros fermentum feugiat vel id velit. Quisque ut congue mi. Duis interdum, ante in lobortis bibendum, nulla ipsum feugiat erat, sit amet rutrum lacus ligula non dolor. Curabitur adipiscing, dolor non semper faucibus, nisl ante euismod nunc, ac facilisis purus neque non nibh. Cras vitae consectetur ipsum. In hac habitasse platea dictumst. Proin vitae nibh sit amet enim aliquet aliquet. Nam commodo sodales nulla, at convallis nulla elementum nec. Vestibulum laoreet nunc id purus feugiat ut eleifend tortor tincidunt. Integer tempus, quam nec lacinia euismod, odio nibh suscipit diam, nec ullamcorper massa felis sit amet elit.
</p>
<p>

Integer pharetra consequat nisi a viverra. Maecenas a eros lacus, vel sagittis velit. Nunc nisl ante, varius non accumsan a, mollis in justo. Vivamus pretium blandit elementum. Nam in elementum ligula. Vivamus at erat urna, quis fermentum enim. Vivamus tempus arcu sit amet dui tincidunt mattis non sed urna. Vivamus urna eros, suscipit sit amet venenatis a, molestie at dui. Proin at consectetur mauris. Pellentesque placerat, tortor non condimentum posuere, est mi posuere odio, a pellentesque nisi lectus quis lorem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Curabitur sollicitudin nisi condimentum mi vehicula nec suscipit dui vulputate. Mauris convallis molestie turpis eu tristique.
</p>
<p>
Sed mattis rhoncus risus, vitae elementum odio consectetur at. Phasellus imperdiet faucibus augue, nec laoreet ante scelerisque semper. Vivamus id libero turpis. Phasellus sed nulla ante. Maecenas aliquam dapibus magna, id tempus dui fermentum at. Etiam consequat, purus a lobortis hendrerit, velit est condimentum nisi, eget pulvinar eros turpis sit amet purus. Nunc eu dolor velit. Nam mollis tempus turpis, eget commodo diam mollis a. Aenean molestie mauris eu lacus lobortis volutpat. Nulla molestie enim id augue lobortis tincidunt. Vivamus commodo mattis pharetra. Phasellus euismod aliquam quam, vitae sollicitudin urna consectetur sed.
</p>
<p>
Cras id sem in purus faucibus gravida eget sed nibh. Cras id augue mi. Aenean facilisis varius metus at tempor. Fusce commodo malesuada vulputate. Duis vehicula vehicula mauris, quis adipiscing ipsum scelerisque vel. Curabitur vel enim eget metus tempor aliquam vitae vitae quam. Nulla quis condimentum massa. Donec viverra ligula et nulla auctor placerat. Phasellus nunc sapien, malesuada non tempor sit amet, convallis a lacus. Sed gravida sagittis sodales. Nunc tempus mattis quam, quis auctor elit viverra ac. Aliquam tempus sollicitudin velit ac suscipit. Suspendisse ac neque non purus sagittis gravida non sit amet mi. Mauris mauris leo, ultrices sed molestie quis, accumsan eu sem. Praesent hendrerit leo eget diam hendrerit facilisis. Donec vel hendrerit risus. Cras blandit tempor orci ut pellentesque. Ut in lorem tincidunt magna feugiat molestie at ut erat.
</p>
<p>
In viverra eros lacus, quis egestas diam. Nulla sodales erat vitae libero dapibus commodo. Maecenas elit libero, congue vel ullamcorper quis, interdum porta nunc. Integer turpis massa, fermentum in ultrices non, placerat eu mauris. Proin ut est diam. Suspendisse neque justo, placerat tempus euismod quis, luctus eget erat. Sed ut sem sit amet orci adipiscing aliquet. Etiam tellus diam, lobortis nec tempus in, vehicula quis arcu. Duis semper eros a mauris mollis at luctus justo iaculis. Fusce lacinia adipiscing est, sed dictum risus rutrum a. Praesent sit amet purus orci, id faucibus mauris. Cras justo arcu, rutrum quis auctor vitae, malesuada in diam. Sed dui quam, vehicula eget tincidunt at, commodo in justo. Suspendisse porttitor dignissim diam, sed sollicitudin nulla commodo at. Etiam facilisis nulla vel sapien pulvinar placerat. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Nulla adipiscing magna a enim interdum sed sollicitudin enim porttitor. Sed eu nisl ac nulla venenatis mollis. Phasellus pharetra consectetur iaculis. Aliquam erat volutpat.
</p>
<p>
Duis vitae lorem vel leo pulvinar aliquam. Praesent aliquet convallis enim ac ultricies. Cras egestas, risus sit amet luctus vehicula, lectus dui volutpat massa, ac congue ante ligula et urna. Vivamus sodales gravida sem sit amet aliquam. Integer pulvinar, enim vel auctor cursus, lectus tellus malesuada erat, et pharetra risus metus accumsan erat. Pellentesque gravida pulvinar fermentum. Nulla luctus velit felis, et malesuada arcu. Proin vulputate sapien in tellus ullamcorper quis consectetur tortor facilisis. In dictum, dui ac ornare pellentesque, velit orci rutrum lectus, vel sagittis nisl arcu ut diam. Ut quis quam erat. Duis at bibendum nibh. Sed euismod, leo id suscipit bibendum, dui justo egestas sem, quis lacinia est velit eget risus. Fusce ac augue arcu, quis egestas velit. Maecenas lacinia, arcu quis auctor venenatis, nisl felis aliquam turpis, ut rutrum metus risus non massa. Suspendisse aliquam cursus lacus, sit amet sodales nulla dapibus at.
</p>
<p>
Nunc sed convallis ipsum. Pellentesque venenatis lacus ac felis scelerisque pharetra. Mauris lacus ante, varius a pretium nec, tempus eget mi. In hac habitasse platea dictumst. Fusce vehicula tincidunt nisl, in viverra felis faucibus a. Quisque euismod ipsum id orci scelerisque in sodales erat mollis. Proin dolor enim, consectetur sed lobortis a, aliquet quis turpis. Ut bibendum iaculis ante et vehicula. Nam facilisis suscipit arcu, non feugiat tellus ultrices sed. Nam nibh enim, pretium eu adipiscing et, placerat id odio. Nulla magna metus, suscipit vel volutpat nec, vehicula ut velit.
</p>

<p>
Etiam feugiat, nulla ut semper venenatis, urna arcu sagittis magna, at pulvinar metus libero eu ligula. Aliquam facilisis dictum nulla, ac imperdiet eros ultrices vel. Sed vestibulum fringilla dolor a tristique. Suspendisse gravida semper leo id hendrerit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed malesuada convallis dignissim. Vestibulum blandit magna quis tellus eleifend porta. Sed elementum neque ut risus placerat id feugiat orci pretium. Quisque nunc ligula, sodales fringilla pulvinar vel, aliquet at velit. Aenean feugiat mollis elit, at ullamcorper lacus tempor vel. Nulla a rutrum dolor. Nam scelerisque pellentesque iaculis. Sed porta, risus non vulputate consectetur, lacus sem imperdiet ligula, non sollicitudin ipsum dui sed ligula. Nullam pellentesque nunc sit amet ligula sollicitudin auctor. Ut aliquet libero vitae ligula egestas a rutrum massa consequat. Aenean ac nisi elit. Sed vehicula libero sed eros placerat adipiscing nec fringilla lacus. Pellentesque placerat dapibus ultricies.
</p>
<p>
Pellentesque sit amet lectus dui. Quisque at erat neque, non porta magna. Aenean id ante in eros cursus pulvinar. Nullam vel velit at nunc porta rutrum vitae id orci. Vivamus eget dolor nibh, et imperdiet leo. Curabitur feugiat, nisi in faucibus placerat, ligula nunc interdum sapien, at eleifend leo mauris eu enim. Nunc vel ipsum ac eros scelerisque faucibus. Integer eu libero erat, eu ullamcorper diam. Cras est neque, gravida sit amet feugiat consectetur, fermentum at lacus. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Mauris quis dui non sem rhoncus egestas id pretium arcu.
</p>
<p>
Nunc in dui felis. Integer tincidunt tincidunt gravida. Sed ante elit, blandit eget egestas sit amet, eleifend id leo. In fringilla dignissim ipsum, sit amet vulputate risus molestie sed. Pellentesque facilisis posuere ante ut rhoncus. Suspendisse rutrum, magna ut viverra eleifend, quam urna molestie arcu, non dictum mauris nisl vitae orci. Fusce congue, eros nec molestie molestie, libero libero sagittis nibh, eget gravida nisi eros id dolor. Suspendisse posuere euismod condimentum. In hac habitasse platea dictumst. Morbi bibendum pharetra tellus. Aliquam varius placerat varius. Pellentesque ut pellentesque leo. Etiam sit amet diam dolor, nec ultrices enim. Aliquam erat volutpat. Etiam vel nisi ac dolor varius pulvinar at eu lacus. Pellentesque id est a lectus faucibus convallis. Donec libero urna, cursus sit amet malesuada sed, iaculis sed risus. Quisque ipsum ligula, placerat et congue vitae, elementum ut felis.
</p>
<p>
In hac habitasse platea dictumst. Aliquam blandit faucibus odio. Nullam in risus laoreet odio fringilla tempus. Nunc consequat lobortis ipsum vel fermentum. Pellentesque et mi lacinia tortor mattis consectetur. Nam eu magna quis dolor mollis scelerisque. Donec risus odio, posuere at pellentesque eu, lobortis nec lorem. In hac habitasse platea dictumst. Sed tincidunt euismod tristique. Nunc placerat erat vitae arcu ultrices in auctor urna adipiscing. Suspendisse elementum nisi laoreet nulla consequat in hendrerit dolor faucibus. Mauris iaculis lobortis tellus et aliquet. Mauris tortor sapien, venenatis nec hendrerit at, venenatis eget justo. Pellentesque venenatis lacus non ligula egestas vitae blandit turpis convallis. Ut vel sem a ligula elementum imperdiet non eget magna. Quisque ultricies varius elit, eget interdum sem adipiscing vel. Morbi nec semper purus. Curabitur rutrum sem at eros elementum eget pharetra diam sagittis. Sed lacus ligula, convallis eu semper et, tincidunt volutpat odio.
</p>
<p>
Suspendisse ac nisi tortor. Phasellus viverra dignissim quam ut aliquam. Proin leo nisi, varius eu pellentesque sit amet, sodales non metus. Nam sit amet leo adipiscing tortor dignissim imperdiet. Quisque massa massa, vehicula ut porta a, accumsan convallis erat. Fusce mauris velit, lacinia id convallis at, sollicitudin eu lectus. Quisque molestie imperdiet justo, eu mattis mauris euismod nec. Cras sollicitudin vehicula hendrerit. Curabitur vehicula congue nibh tempus feugiat. Cras vitae sem risus, sed sodales neque. Donec semper urna eget orci ultricies facilisis. Sed at lectus nibh, sed molestie lorem. Proin elit mauris, tempor sed scelerisque a, aliquet eget erat. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas id purus sit amet urna rutrum iaculis sit amet laoreet ligula. Fusce lacus nisl, dictum ut adipiscing et, pulvinar vel nisl. Pellentesque ut bibendum mi. Ut mollis lectus eu nisi ultrices pellentesque. Pellentesque ac purus tellus. Etiam vulputate, lacus eu mattis dictum, arcu turpis pellentesque orci, eu dignissim lorem mauris facilisis lorem.
</p>
<p>
Nunc eu magna nisl, semper tincidunt sem. Maecenas quis malesuada tellus. Mauris elementum dapibus eros, et laoreet metus dignissim eget. Duis luctus, augue quis blandit semper, nisl ipsum semper libero, eget semper magna sapien et tellus. Donec fringilla dolor in felis vulputate feugiat. Nullam scelerisque ipsum iaculis orci semper lacinia. Nam iaculis elementum tortor pharetra bibendum. Morbi vitae aliquet enim. Maecenas nulla ligula, tempus et cursus non, rutrum et est. Nullam tellus magna, blandit eget pretium at, fringilla quis tellus. Vestibulum a odio eget nibh blandit condimentum lobortis sed felis. Donec lorem purus, feugiat ut tempus dictum, lobortis eget nisl. In vel mauris tortor. Sed pellentesque iaculis libero, eu pretium magna aliquam in. Cras facilisis accumsan dolor, vitae molestie erat porttitor nec. Morbi lectus elit, tempor ac tempor sit amet, tristique non est. Cras aliquet dolor eget magna euismod luctus. Suspendisse convallis lectus sem, id sagittis risus. Maecenas lacus erat, egestas nec tincidunt mattis, pulvinar sed erat. Duis ligula justo, gravida sed egestas sed, eleifend sed ante.

</p>
<p>
Proin varius, purus sed vulputate vulputate, nisi magna bibendum neque, at tincidunt augue elit quis tortor. Donec nec turpis lorem. Integer eget lacus ut arcu lobortis cursus. Nam ultricies risus rhoncus dui ullamcorper pretium. Proin ipsum velit, gravida sit amet suscipit in, tempor sit amet purus. Pellentesque interdum, erat non egestas rutrum, leo risus commodo lectus, quis tincidunt arcu metus et lacus. In in leo non risus lacinia varius tincidunt condimentum dolor. Ut in nulla eu nisl mattis scelerisque. Nunc mattis leo sed quam aliquet ac lacinia nisi hendrerit. Pellentesque varius sem ut dui placerat varius. Fusce aliquet semper molestie. Sed ac mi eu enim euismod pellentesque. Nullam dui orci, ultrices ac feugiat id, placerat sed turpis. Aliquam erat volutpat. Vestibulum velit lorem, adipiscing quis iaculis sed, adipiscing a lorem. Morbi quis nisi nisl, ac tincidunt eros. Suspendisse accumsan, odio et accumsan auctor, velit neque fermentum tellus, non faucibus dui magna tempor lectus. Aliquam quam tellus, tempor in dapibus vitae, rutrum eu felis. Maecenas ultrices suscipit sapien, ut tincidunt metus scelerisque eu. Morbi volutpat odio a nibh condimentum posuere.
</p>
<p>
Mauris vitae justo vel turpis ornare feugiat a varius quam. Etiam vel luctus lacus. Vivamus pellentesque sodales ante eget porttitor. Nullam vel orci augue, ac vehicula elit. Sed non orci velit. Nam in nisi eget lectus pretium porttitor at ut quam. Ut vel nisl at augue fringilla cursus. Aliquam lacus diam, molestie vel pellentesque in, interdum eu risus. Etiam semper bibendum condimentum. Donec ultrices egestas arcu, non vehicula est convallis ullamcorper. Maecenas fringilla purus sed eros mattis venenatis. Phasellus nunc odio, porttitor a placerat id, luctus eget nunc. Ut massa urna, tristique at fringilla eget, sollicitudin et sem. Etiam mollis, augue a adipiscing pharetra, massa metus placerat augue, in eleifend magna diam ultricies massa. Nullam suscipit ligula non ante lobortis id posuere felis molestie. Integer ultrices nunc at velit malesuada at hendrerit lorem euismod. Curabitur quis nisi dui.
</p>
<p>
Morbi posuere, orci non lobortis semper, massa neque scelerisque urna, tincidunt fringilla velit purus eget augue. Morbi interdum blandit metus at cursus. Fusce quis venenatis mauris. In hac habitasse platea dictumst. Nullam quis erat augue, sit amet rutrum neque. Aliquam sagittis mattis orci, nec aliquet sem congue sed. Donec luctus felis eu ante molestie eget tempor eros malesuada. Vivamus sed mauris sit amet sapien mattis elementum. Praesent vehicula, diam at porttitor sodales, turpis libero facilisis nibh, in tristique ligula velit non mi. Etiam iaculis molestie consectetur. Integer libero ligula, porta at fermentum ac, faucibus imperdiet purus. Maecenas molestie lectus id risus venenatis porttitor. Nulla ac felis eu ante molestie ornare. Phasellus posuere rutrum sapien pretium imperdiet. Suspendisse porttitor sagittis fringilla.
</p>
<p>
Vivamus scelerisque, nunc quis tempus faucibus, tortor risus viverra erat, ac egestas magna lacus ac mi. Etiam leo augue, vulputate id iaculis eget, lobortis nec libero. Proin sit amet ante et lectus consectetur auctor. Pellentesque tempor risus id erat placerat ac malesuada lacus malesuada. Etiam at risus ut nisl varius iaculis. Proin vitae lectus magna, non ultrices risus. Nulla posuere leo facilisis metus tristique ut auctor libero interdum. Mauris molestie purus eu ligula pellentesque vulputate cursus sapien malesuada. In eu odio leo. Nullam condimentum blandit dapibus. Curabitur hendrerit quam ut erat iaculis ornare. Ut eleifend nisl sit amet nisi mollis elementum eget eget nunc. Phasellus a erat ultricies libero mollis ultrices. Mauris consectetur urna sit amet odio cursus nec dictum sapien egestas. Mauris eu quam nunc.
</p>
<p>
Maecenas cursus augue in nulla facilisis et fermentum ante vulputate. Aliquam erat volutpat. Vivamus justo quam, accumsan a tempus eget, semper eget quam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Vestibulum sed risus egestas nunc ullamcorper fringilla. Aenean auctor ante vitae neque fringilla a viverra arcu rutrum. Pellentesque eget nisi urna, eget mattis magna. Donec metus velit, semper sed porta eu, ornare sit amet ipsum. Sed a magna in augue vulputate pellentesque. Cras scelerisque dui vel neque elementum bibendum. Donec lobortis, augue ac laoreet faucibus, felis mi eleifend leo, vitae hendrerit nulla massa a urna. Integer tincidunt rhoncus dui, ut tristique lectus sodales ut. Etiam ut turpis neque. Donec fringilla, lorem nec porta blandit, nisl leo pretium ante, sit amet sodales ante mi sit amet justo. Phasellus volutpat tempor semper. Praesent non metus sed libero viverra gravida sit amet ut mi. Pellentesque egestas interdum neque et tempus. Pellentesque metus purus, consequat ac sodales nec, scelerisque eget augue. Nam eu ligula non mi interdum lacinia. Vestibulum dui velit, accumsan in consectetur nec, malesuada et risus.
</p>
<p>

Praesent eu vulputate nunc. Suspendisse massa nisi, suscipit vel placerat eget, mattis vel enim. Fusce tincidunt est eget leo molestie ornare. Fusce malesuada, ante adipiscing malesuada consectetur, urna augue faucibus nunc, vitae porta turpis neque nec justo. Vestibulum egestas ullamcorper neque, nec scelerisque eros condimentum sed. Suspendisse suscipit, orci et scelerisque imperdiet, orci nulla pretium enim, vitae luctus enim sapien ut orci. Praesent sit amet libero enim. Sed fermentum turpis vel tortor consequat id facilisis orci fermentum. Praesent sed mi sit amet odio interdum pretium. Praesent convallis gravida ipsum a condimentum. Nullam eu erat metus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent suscipit ante a ante dapibus quis iaculis massa aliquam. Aenean imperdiet diam et elit malesuada vitae convallis sem facilisis. Nam nec leo massa, id laoreet sapien. Nulla interdum placerat eros non eleifend. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Praesent porta eleifend quam a lobortis.
</p>
<p>
Praesent quis enim quam, ut porttitor lectus. Pellentesque ipsum mauris, interdum at ultrices a, viverra sit amet lacus. Vivamus luctus orci non tortor ornare varius. Nunc vitae neque dapibus nunc tristique dictum sit amet ut eros. Nam imperdiet lectus nec leo luctus nec elementum justo malesuada. Sed lectus risus, iaculis nec vulputate in, congue a ligula. Donec urna nibh, pellentesque quis pellentesque at, suscipit sit amet lacus. Nulla sed ipsum arcu, porta consequat metus. Nullam et velit lacus, at pellentesque tortor. Etiam sodales felis et mauris commodo a pharetra nunc blandit. Suspendisse ullamcorper rutrum fringilla. Pellentesque ac libero odio, at condimentum felis. Integer condimentum viverra turpis, in lobortis dui commodo non. Donec bibendum mollis risus vel gravida. Ut consectetur interdum fermentum. Mauris a felis purus, malesuada pulvinar erat. Sed a sem eu neque adipiscing congue eu eu neque. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.
</p>
<p>
Aliquam dignissim dignissim risus consequat suscipit. Donec tempus malesuada dui, condimentum gravida lorem porta vel. Fusce non erat ipsum, a accumsan velit. Morbi ut justo arcu, ut suscipit dolor. Ut luctus nunc vel eros adipiscing nec iaculis eros interdum. Nullam placerat tellus nulla. Donec elit eros, tristique nec interdum et, sagittis in neque. Praesent dui nibh, faucibus vel varius non, pulvinar eu enim. Maecenas neque nibh, feugiat varius iaculis ac, interdum vitae nisi. Vivamus a ante in sapien tempor sollicitudin. Aliquam lectus turpis, consequat non suscipit sit amet, condimentum nec velit.
</p>
<p>
In diam dolor, ornare in consectetur eget, sagittis id neque. Pellentesque condimentum tincidunt lectus, in scelerisque nunc commodo id. Aenean eget venenatis ipsum. In cursus tincidunt quam, et imperdiet orci ullamcorper in. Morbi tristique gravida ante, et dignissim massa euismod ut. Nam porttitor turpis et neque luctus quis congue dui adipiscing. Nam laoreet vestibulum massa, non laoreet lectus hendrerit id. Morbi id quam ac ligula tempus sagittis. Phasellus suscipit, orci mattis adipiscing imperdiet, ipsum leo iaculis erat, a imperdiet velit turpis at felis. Integer vitae blandit dui. Vivamus vestibulum euismod nisi ut interdum. Pellentesque erat ante, pharetra sed suscipit vel, mattis id felis. Morbi interdum faucibus mollis. Pellentesque vitae sollicitudin elit. Fusce semper eleifend egestas.
</p>
<p>
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque et sagittis arcu. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Donec quis diam neque. Aenean vitae nisl vel lacus interdum blandit eget eget lorem. Sed sem purus, semper eget egestas non, ultricies non urna. Proin lacus lectus, rutrum et cursus quis, scelerisque sit amet purus. Praesent non velit sed nisl malesuada lobortis ac sit amet eros. Vivamus at enim laoreet nisl vulputate consequat quis vel ipsum. Mauris lacus justo, venenatis eu dignissim vel, vehicula vel leo. Donec laoreet mollis quam, ut accumsan eros commodo a. Nulla luctus mi at nunc imperdiet sit amet gravida quam consectetur. In ut lacus ac massa luctus semper. Vestibulum dictum mauris ut metus vulputate quis malesuada tellus tincidunt. Praesent tempus dolor et nibh ullamcorper at consectetur neque euismod. Vestibulum est nisl, tempor et faucibus sed, bibendum semper erat.
</p>
<p>
Fusce vehicula dictum turpis. Maecenas iaculis blandit nulla, eget cursus ligula accumsan quis. Nulla mollis tempor hendrerit. Nam a turpis tortor, fringilla fringilla erat. Etiam fringilla condimentum consequat. Phasellus sit amet molestie lacus. Fusce sollicitudin imperdiet nunc non imperdiet. Aliquam nec erat nulla. Vestibulum lacus est, ultrices nec tempor et, venenatis ac massa. Vivamus vel lorem a diam pharetra scelerisque id luctus odio.
</p>

<p>
Nam ut dolor ut lectus tincidunt consectetur. Nam varius euismod odio malesuada dignissim. Nulla eu eleifend ante. Quisque a egestas purus. Quisque sed augue et velit rhoncus cursus. Nullam venenatis pellentesque elit sit amet convallis. Duis eros nunc, laoreet eu cursus sed, rutrum nec justo. Nam ornare fringilla augue quis pulvinar. Vivamus venenatis varius lorem, a aliquam metus mollis quis. Quisque semper mollis viverra. Morbi gravida mattis sollicitudin. Vivamus in velit a tortor rutrum eleifend. Suspendisse fermentum, lectus id lobortis tempor, leo tortor faucibus nisi, fermentum rhoncus felis est ac enim. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam ut ultricies neque.
</p>
<p>
Curabitur a turpis nisi, vitae lacinia purus. Donec eu placerat diam. Donec tempor nisl eu est faucibus at adipiscing orci convallis. Nam bibendum, risus vitae rutrum egestas, purus eros aliquam nisi, ac feugiat urna nisi in turpis. Curabitur ultricies justo ac tortor suscipit commodo. Ut vestibulum elit id felis mattis tempor. Pellentesque scelerisque ultricies feugiat. In hac habitasse platea dictumst. Morbi imperdiet semper iaculis. Vestibulum pulvinar, eros quis tristique tristique, augue enim sodales turpis, ut rutrum libero velit sed dolor. Suspendisse potenti. Integer vehicula, diam ac pharetra porttitor, ipsum magna mollis felis, at condimentum dolor sapien vitae metus. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vivamus ac neque vitae quam bibendum aliquam. Suspendisse ultrices dui at leo tempor hendrerit ultricies purus posuere. Morbi ultrices lobortis facilisis. Sed pretium, lacus non malesuada iaculis, dui dui ultrices lacus, id pellentesque enim diam vitae lacus. Sed volutpat rutrum magna eleifend rhoncus.
</p>
<p>
Quisque ut tempor urna. Donec eget fringilla tellus. Nulla vehicula metus vitae tellus vestibulum ullamcorper. Ut eu lorem quis augue cursus commodo at et mauris. Phasellus sollicitudin magna a erat accumsan egestas. Sed sed dolor id elit convallis porta. Etiam id purus in quam suscipit tempor. Nam eget ante sem, sit amet fermentum nunc. Vestibulum laoreet feugiat mauris eget adipiscing. Ut et nisi nulla, sed pellentesque lacus. Donec non congue nisi. Aliquam erat volutpat. Nulla facilisi. Integer rhoncus lacus eu leo condimentum eget facilisis eros scelerisque. Curabitur diam nisl, blandit sit amet bibendum ut, tempus eu urna. Nulla enim risus, pulvinar eu luctus at, fringilla sit amet lectus. Sed vel libero sem. Sed turpis dolor, dapibus quis placerat vel, suscipit eu sem.
</p>
<p>
Aliquam iaculis leo eget dolor vulputate dignissim. Quisque tortor enim, commodo et semper a, hendrerit at urna. Sed purus arcu, scelerisque dapibus bibendum in, rhoncus vitae felis. Proin consequat imperdiet suscipit. Phasellus tempus cursus ligula. Ut posuere, nibh et sagittis elementum, diam nisl consequat tellus, ut laoreet magna dolor ut turpis. Suspendisse potenti. Fusce non varius elit. Quisque metus lorem, mattis quis gravida nec, tempus et quam. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus.
</p>
<p>
Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aliquam sollicitudin semper aliquam. Quisque lectus neque, mollis at blandit quis, lobortis non enim. Fusce ac neque mauris. Maecenas elementum velit eget mauris auctor ac pellentesque diam adipiscing. Mauris augue sapien, dapibus a pretium ac, sagittis suscipit eros. Donec et quam eget mi malesuada varius. Sed dictum elit et neque pellentesque consectetur. Phasellus interdum bibendum tempus. Duis mollis nibh nec neque tincidunt vitae fermentum elit pretium. Sed quis elit ut urna convallis commodo nec sed elit. Duis sed dolor eget dui gravida tincidunt et nec mauris. In hac habitasse platea dictumst. Maecenas id nisl eget ante egestas tincidunt.
</p></div>

<div><a href="https://lipsum.com/" title="Lorem Ipsum">Lorem Ipsum</a></div>

<?php

if (isset($debeetleException)) {
    printf(
        "<div style=\"position: fixed; top: 2px; left: 3px; display: block; padding: 2px 3px; border: 1px dashed red; color: red; background: #EEE;\"><b>%s: %s</b> at %s(%d)\n<pre>%s</pre></div>\n",
        get_class($debeetleException),
        $debeetleException->getMessage(),
        $debeetleException->getFile(),
        $debeetleException->getLine(),
        $debeetleException->getTraceAsString()
    );
    unset($debeetleException);
} else {
    /**
     * @var deepeloper\Debeetle\DebeetleInterface $debeetle
     */
    $debeetle = d::getInstance();
    if ($debeetle) {
        $settings = $debeetle->getSettings();
        d::t("environment|includedFiles");
        d::dump(
        "debeetle" === $settings['bench']['includedFiles']['exclude']
            ? $debeetle->getExternalIncludedFiles()
            : get_included_files()
        );
        echo $debeetle->getView()->get();
    }
    unset($debeetle, $settings);
}
?>
</body>
</html>
