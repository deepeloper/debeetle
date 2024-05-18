<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Skin\Custom\Theme\ByDefault;

use deepeloper\Debeetle\Skin\Theme\AbstractController;

/**
 * Default theme.
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
