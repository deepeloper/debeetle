<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Plugin;

use deepeloper\Debeetle\DebeetleInterface;

interface ControllerInterface
{
    /**
     * Returns plugin path.
     *
     * @return string
     */
    public static function getPath();

    /**
     * Sets debeetle instance.
     *
     * @param DebeetleInterface $debeetle
     * @param string $id
     * @return void
     */
    public function setInstance(DebeetleInterface $debeetle, $id);

    /**
     * Initialize plugin.
     *
     * @return void
     */
    public function init();

    /**
     * Displays settings if necessary.
     *
     * @return void
     */
    public function displaySettings();

    /**
     * Processes separate request and returns data if necessary.
     *
     * @retun mixed
     */
    public function processRequest();
}
