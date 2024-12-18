<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\View;

use deepeloper\Debeetle\Tree\Tree;

interface ViewInterface
{
    public function addLocales(array $locales, $override = false);

    /**
     * Returns code initializing debugger.
     *
     * Usage examples:
     * <code>
     * d::get()->getView()->get();
     * // will return JavaScript code initializing Debeetle
     * // <script type="text/javascript">
     * // <!--
     * // $d.startup(
     * // ...
     * // );
     * // -->
     * // </style>
     * </code>
     *
     * @return string  Appropriate HTML code
     */
    public function get();

    /**
     * Set tab object
     *
     * @param ?Tree $tab
     * @return void
     */
    public function setTab(?Tree $tab = null);

    /**
     * Render string
     *
     * @param  string $string   String
     * @param  array  $options  Reserved array for functionality enhancement
     * @return string
     * @see    Debeetle::write()
     */
    public function renderString($string, array $options = []);
}
