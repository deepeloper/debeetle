<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Tree;

/**
 * Empty tabs tree element.
 */
class EmptyNode extends Node
{
    /**
     * Send data to the tab
     *
     * @param  string $data  Data to send
     * @return void
     */
    public function send($data)
    {
    }

    /**
     * Returns tab content
     *
     * @return null
     */
    public function get()
    {
        return null;
    }
}
