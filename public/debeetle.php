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

// SET UP PATH to "autoload.php".
$autoloadPath = realpath("path/to/autpload");
// SET UP PATH to Debeetle XML/JSON (JSON parses faster) config.
$configPath = realpath("path/to/xml/or/json/config");
require_once $autoloadPath;

new PublicAsset($configPath, $_GET);
