<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Skin\ByDefault;

use deepeloper\Debeetle\Skin\AbstractController;

/**
 * Default skin.
 */
class Controller extends AbstractController
{
    /**
     * Returns skin path.
     *
     * @return string
     */
    public static function getPath()
    {
        return realpath(__DIR__);
    }
}
