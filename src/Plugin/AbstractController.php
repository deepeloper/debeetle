<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Plugin;

use deepeloper\Debeetle\DebeetleInterface;

abstract class AbstractController implements ControllerInterface
{
    /**
     * Debugger instance
     *
     * @var DebeetleInterface
     */
    protected $debeetle;

    /**
     * Settings
     *
     * @var array
     */
    protected $settings;

    /**
     * @var string
     */
    protected $id;

    /**
     * Sets debeetle instance.
     *
     * @param DebeetleInterface $debeetle
     * @param string $id
     * @return void
     */
    public function setInstance(DebeetleInterface $debeetle, $id)
    {
        $this->debeetle = $debeetle;
        $this->settings = &$debeetle->getSettings();
        $this->settings['eol'] =
            isset($this->settings['eol'])
                ? str_replace(
                    ['\n', '\r'],
                    ["\n", "\r"],
                    $this->settings['eol']
                )
                : PHP_EOL;
        $this->id = $id;
    }

    /**
     * Displays settings.
     *
     * @return void
     */
    public function displaySettings()
    {
    }

    /**
*     * Processes separate request and returns data (stub).
     *
     * @retun mixed
     */
    public function processRequest()
    {
        return null;
    }

    protected function setOptionsType(array &$options, array $names, $type)
    {
        foreach ($names as $name) {
            settype($options[$name], $type);
        }
    }

    protected function patchBooleanOptions(array &$options, array $names)
    {
        foreach ($names as $name) {
            if (!is_bool($options[$name])) {
                $options[$name] = "true" === strtolower($options[$name]);
            }
        }
    }
}
