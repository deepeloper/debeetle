<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * @var array $settings
 */

new Asset(__DIR__, $_GET, $settings);

