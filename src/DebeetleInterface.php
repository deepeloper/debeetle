<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle;

use deepeloper\Debeetle\View\ViewInterface;

/**
 * Debeetle interface.
 */
interface DebeetleInterface
{
    /**
     * Magic caller.
     *
     * @param string $method  Method name
     * @param array $args  Arguments
     * @return mixed
     */
    public function __call($method, array $args);

    /**
     * Saves method caller.
     *
     * @param int $offset  Offset in debug_backtrace() result
     * @return void
     */
    public function setTrace($offset);

    /**
     * Returns method caller.
     *
     * @return array
     */
    public function getTrace();

    /**
     * Resets method caller.
     *
     * @return void
     */
    public function resetTrace();

    /**
     * Registers method.
     *
     * @param string $name  Method name
     * @param callable $handler  Method handler
     * @param bool $override  Override existent handler
     * @return void
     */
    public function registerMethod($name, callable $handler, $override = false);

    /**
     * Calls passed method of each registered plugin.
     *
     * @param string $method  Method
     * @param array $args  Arguments
     * @return void
     */
    public function callPluginMethod($method, array $args = []);

    /**
     * Returns settings.
     *
     * @return array
     */
    public function getSettings();

    /**
     * Sets Debeetle instance to the plugins.
     *
     * @return void
     */
    public function setInstance();

    /**
     * Sets view instance.
     *
     * @param ViewInterface $view  View object
     * @return void
     */
    public function setView(ViewInterface $view);

    /**
     * Returns view instance.
     *
     * @return ViewInterface
     */
    public function getView();

    /**
     * Sets default options for methods supporting options.
     *
     * @param string $target  Target method name
     * @param array $options  Array of options
     * @return void
     */
    public function setDefaultOptions($target, array $options);

    /**
     * Specifies target tab.
     *
     * @param string $id  Tab id
     * @param ?string $name Pure caption or null when should be loaded from locales
     * @param ?array $places Target places (f.e. ["before:tabId", "after:tabId", "start:tabId", "end:tabId", "anywhere"])
     * @param array $options
     * @return void
     */
    public function tab($id, $name = null, array $places = null, array $options = []);

    /**
     * Writes string to debug output.
     *
     * @param string $string  String to write
     * @param array $options  Reserved array for functionality enhancement
     * @return void
     * @todo Describe options.
     */
    public function write($string, array $options = []);

    /**
     * Verifies printing data by label condition.
     *
     * @param string $method  Debeetle method name
     * @param string $label  Label
     * @param array $options  Options
     * @return bool
     */
    public function checkLabel($method, $label, array $options);

    /**
     * Returns internal benches.
     *
     * @return array|null
     */
    public function getInternalBenches();

    /**
     * Adds Debeetle path to avoid counting for included files number.
     *
     * @param string $path
     * @return void
     */
    public function addPath($path);

    /**
     * Returns list of external (not Debeetle) files.
     *
     * @return array
     */
    public function getExternalIncludedFiles();
}
