<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle;

use deepeloper\Debeetle\Exception\NoInstanceException;

/**
 * Static and short call support for Debeetle.
 *
 * @static
 * @method static dump($entity, $label = "", array $options = [])
 * @method static du($entity, $title = "", array $options = [])
 * @method static trace()
 * @method static sb($label)
 * @method static eb($label)
 * @method static getBenchmarks()
 * @method static cp($label, array $options = [])
 * @method static getCheckpoints()
 */
class d
{
    /**
     * Instance
     *
     * @var Debeetle
     */
    protected static $instance;

    /**
     * @var bool
     */
    protected static $skip;

    /**
     * Sets Debeetle instance.
     *
     * @param DebeetleInterface $instance
     * @return void
     */
    public static function setInstance(DebeetleInterface $instance)
    {
        self::$instance = $instance;

        $settings = $instance->getSettings();
        $request = HTTPRequest::getInstance();
        $cookie = $request->get($settings['cookie']['name'], null, "c");
        self::$skip = empty($cookie);
    }

    /**
     * Returns instance
     *
     * @return DebeetleInterface
     * @throws NoInstanceException
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            $settings = Loader::getSettings();
            if (empty($settings['deepeloperMode'])) {
                return null;
            }
            Loader::onError("No Debeetle instance", "NoInstanceException");
        }
        return self::$instance;
    }

    /**
     * Calls plugins methods.
     *
     * @param  string $name  Method name
     * @param  array  $args  Method arguments
     * @return mixed
     */
    public static function __callStatic($name, array $args)
    {
        if (self::$skip) {
            return null;
        }
        $instance = self::getInstance();
        $result = null;
        if ($instance) {
            $instance->setTrace(1);
            $result = call_user_func_array([$instance, $name], $args);
        }
        return $result;
    }

    /**
     * @param string $id  Tab id
     * @param ?string $name Pure caption or null when should be loaded from locales
     * @param ?array $places Target places (f.e. ["before:tabId", "after:tabId", "start:tabId", "end:tabId", "anywhere"])
     * @param array $options
     * @return void
     * @see Debeetle::tab()
     * @see Tree::select()
     */
    public static function t($id, $name = null, array $places = null, array $options = [])
    {
        if (self::$skip) {
            return;
        }
        $instance = self::getInstance();
        if ($instance) {
            $instance->tab($id, $name, $places, $options);
        }
    }

    /**
     * @param  string $string   String to write
     * @param  array  $options  Reserved array for functionality enhancement
     * @return void
     * @see    Debeetle::write()
     */
    public static function w($string, array $options = [])
    {
        if (self::$skip) {
            return;
        }
        $instance = self::getInstance();
        if ($instance) {
            $instance->write($string, $options);
        }
    }
}
