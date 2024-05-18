<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * Resource inclusion service standalone file.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle;

error_reporting(E_ALL);
ini_set("display_errors", 1);

$debeetlePath = realpath("") . "/..";

require_once "$debeetlePath/vendor/autoload.php";

// @todo Describe usage.
new PublicAsset("$debeetlePath/config.xml.php", $_GET);
// new PublicAsset("$debeetlePath/config.json.php", $_GET);
