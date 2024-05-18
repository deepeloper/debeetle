<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Tree;

/**
 * Tabs tree element.
 */
class Node
{
    /**
     * Node name
     *
     * @var bool
     */
    protected $name;

    /**
     * Activity flag
     *
     * @var bool
     */
    protected $active;

    /**
     * Disabled flag
     *
     * @var bool
     */
    protected $disabled;

    /**
     * Tab content
     *
     * @var string
     */
    protected $content = "";

    /**
     * Constructor
     *
     * @param string $name
     * @param bool   $active
     */
    public function __construct($name, $active, $disabled = false)
    {
        $this->name   = $name;
        $this->active = $active;
        $this->disabled = $disabled;
    }

    /**
     * Send data to the tab
     *
     * @param  string $data  Data to send
     * @return void
     */
    public function send($data)
    {
        if (!$this->disabled) {
            $this->content .= $data;
        }
    }

    /**
     * Returns node content
     *
     * @return string
     */
    public function get()
    {
        return $this->content;
    }

    /**
     * Returns node name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->disabled;
    }
}
