<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\View;

use deepeloper\Debeetle\Tree\Tree;

/**
 * Stub view class doing nothing.
 */
class Stub implements ViewInterface
{
    public function addLocales(array $locales, $override = false)
    {
    }

    /**
     * Returns code initializing debugger.
     * @return string  Appropriate HTML code
     */
    public function get()
    {
        return "";
    }

    /**
     * Set tab object
     *
     * @param  Tree $tab
     * @return void
     */
    public function setTab(Tree $tab = null)
    {
    }

    /**
     * Render string
     *
     * @param  string $string   String
     * @param  array  $options  Reserved array for functionality enhancement
     * @return string
     * @see    Stub::write()
     */
    public function renderString($string, array $options = [])
    {
        return '';
    }
}
