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

// SET UP PATH TO "autoload.php"
$autoloadPath = realpath("./../vendor/autoload.php");
//$autoloadPath = realpath("./../../debeetle/vendor/autoload.php");
// SET UP PATH TO Debeetle XML/JSON (JSON parses faster) config
$configPath = realpath("./config.xml.php");
require_once $autoloadPath;

new PublicAsset($configPath, $_GET);
