<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Plugin\PHPInfo;

use deepeloper\Debeetle\Plugin\AbstractController;

/**
 * Debeetle phpinfo() plugin.
 */
class Controller extends AbstractController
{
    /**
     * Plugin version
     *
     * Used for building url hash.
     *
     * @see Debeetle_Resource_Public::processRequest()
     */
    const VERSION = "1.0.0";

    /**
     * Returns plugin path.
     *
     * @return string
     */
    public static function getPath()
    {
        return realpath(__DIR__);
    }

    /**
     * Initialize plugin.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Process separate request and return data (stub)
     *
     * @retun string
     */
    public function processRequest()
    {
        ob_start();
        phpinfo();
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        die(ob_get_clean());
    }
}
