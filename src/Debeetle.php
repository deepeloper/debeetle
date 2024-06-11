<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle;

use deepeloper\Debeetle\Exception\DuplicateMethodException;
use deepeloper\Debeetle\Tree\Tree;
use deepeloper\Debeetle\View\ViewInterface;
use deepeloper\Debeetle\View\HTML;

/**
 * Main Debeetle class.
 *
 * @todo Implement <dump labelTraceOffset="0" labelMaxCount="0"/> ?
 */
class Debeetle implements DebeetleInterface
{
    /**
     * Specifies to skip any actions if true
     *
     * @var bool
     */
    protected $skip = true;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Tabs storage
     *
     * @var Tree
     */
    protected $tab;

    /**
     * view
     *
     * @var ViewInterface
     */
    protected $view;

    /**
     * Default options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Array of printed labels to limit output by label
     *
     * @var array
     */
    protected $labels = [];

    /**
     * Plugins, array containing class names as keys and objects as values
     *
     * @var array
     */
    protected $plugins = [];

    /**
     * Virtual methods
     *
     * @var array
     */
    protected $methods = [];

    /**
     * Trace info
     *
     * @var ?array
     */
    protected $trace = null;

    /**
     * Internal benches
     *
     * @var array
     */
    protected $bench;

    /**
     * @var array
     * @see self::addPath()
     */
    protected $paths = [];

    protected $onShutdown = [];

        /**
     * Constructor.
     *
     * @param array $settings  Array of settings
     * @see Loader::startup()
     */
    public function __construct(array $settings)
    {
        $this->init($settings);
    }

    public function isLaunched()
    {
        return !$this->skip;
    }

        /**
     * Magic caller.
     *
     * @param string $method  Method name
     * @param array $args  Arguments
     * @return mixed
     * @see Debeetle::registerMethod()
     * @see Debeetle_TraceAndRun::init()
     */
    public function __call($method, array $args)
    {
        $result = null;

        if (isset($this->methods[$method])) {
            $useIntarnalBenches =
                !(
                    null !== $this->methods[$method]['optionsArgIndex'] &&
                    !empty($args[$this->methods[$method]['optionsArgIndex']]['skipInternalBench'])
                );

            if ($useIntarnalBenches) {
                $this->startInternalBench();
            }

            $this->setTrace(1);
            $result = call_user_func_array($this->methods[$method]['handler'], $args);
            $this->resetTrace();

            if ($useIntarnalBenches) {
                $this->finishInternalBench();
            }
        }

        return $result;
    }

    /**
     * Saves method caller.
     *
     * @param int $offset  Offset in debug_backtrace() result
     * @return void
     */
    public function setTrace($offset)
    {
        if (null === $this->trace) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            while (!isset($trace[$offset]['file']) && isset($trace[$offset])) {
                $offset++;
            }
            $this->trace = [
                'file' => $trace[$offset]['file'],
                'line' => $trace[$offset]['line']
            ];
        }
    }

    /**
     * Returns method caller.
     *
     * @return array
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * Resets method caller.
     *
     * @return void
     */
    public function resetTrace()
    {
        $this->trace = null;
    }

    /**
     * Registers method.
     *
     * @param string $name  Method name
     * @param callable $handler  Method handler
     * @param ?int $optionsArgIndex  $options argument index
     * @param ?bool $override  Override existent handler
     * @return void
     * @throws DuplicateMethodException
     */
    public function registerMethod($name, callable $handler, $optionsArgIndex = null, $override = false)
    {
        // Check if method is already registered
        if (!$override && isset($this->methods[$name])) {
            Loader::onError(
                "Method $name is already registered",
                "DuplicateMethodException"
            );
        }
        // Collect plugins objects
        $this->plugins[is_object($handler[0]) ? get_class($handler[0]) : $handler[0]] = $handler[0];
        $this->methods[$name] =[
            'handler' => $handler,
            'optionsArgIndex' => $optionsArgIndex,
        ];
    }

    /**
     * Calls passed method of each registered plugin.
     *
     * @param string $method  Method
     * @param array $args  Arguments
     * @return void
     */
    public function callPluginMethod($method, array $args = [])
    {
        foreach (array_keys($this->plugins) as $plugin) {
            call_user_func_array([$this->plugins[$plugin], $method], $args);
        }
    }

    /**
     * Returns settings.
     *
     * @return array
     */
    public function &getSettings()
    {
        return $this->settings;
    }

    /**
     * Sets Debeetle instance to the plugins.
     *
     * @return void
     */
    public function setInstance()
    {
        foreach (array_keys($this->plugins) as $plugin) {
            $this->plugins[$plugin]->setInstance($this);
        }
    }

    /**
     * Sets view instance.
     *
     * @param ViewInterface $view  View object
     * @return void
     */
    public function setView(ViewInterface $view)
    {
        $this->view = $view;
    }

    /**
     * Returns view instance.
     *
     * @return HTML
     */
    public function getView()
    {
        if (!$this->view) {
            $this->view = new HTML($this->settings);
            $this->view->setTab($this->tab);
        }
        return $this->view;
    }

    /**
     * Sets default options for methods supporting options.
     *
     * @param string $target  Target method name
     * @param array $options  Array of options
     * @return void
     */
    public function setDefaultOptions($target, array $options)
    {
        if ($this->skip) {
            return;
        }
        $this->settings['defaults']['options'][$target] = $options;
        $this->setInstance();
    }

    /**
     * Specifies target tab.
     *
     * @param string $id  Tab id
     * @param ?string $name Pure caption or null when should be loaded from locales
     * @param ?array $places Target places (f.e. ["before:tabId", "after:tabId", "start:tabId", "end:tabId", "anywhere"])
     * @param array $options
     * @return void
     * @see Tree::select()
     * @see d::t()
     * @todo Describe options.
     */
    public function tab($id, $name = null, array $places = null, array $options = [])
    {
        if ($this->skip) {
            return;
        }

        if (empty($options['skipInternalBench'])) {
            $this->startInternalBench();
        }

        $caption = null;
        if (is_string($name)) {
            $caption = str_replace("\\", "\\\\", $name);
            if (isset($this->options['write'])) {
                $options += $this->options['write'];
            }
            if (
                isset($options['encoding']) &&
                empty($options['skipEncoding']) &&
                "UTF-8" !== strtoupper($options['encoding'])
            ) {
                $caption = mb_convert_encoding($caption, "UTF-8", $options['encoding']);
            }
        }

        $this->tab->select($id, $caption, $places);

        if (empty($options['skipInternalBench'])) {
            $this->finishInternalBench();
        }
    }

    /**
     * Writes string to debug output.
     *
     * @param string $string  String to write
     * @param array $options  Reserved array for functionality enhancement
     * @return void
     * @see d::w()
     * @todo Describe options.
     */
    public function write($string, array $options = [])
    {
        if ($this->skip) {
            return;
        }

        if (empty($options['skipInternalBench'])) {
            $this->startInternalBench();
        }

        $string = str_replace("\\", "\\\\", $string);
        if (isset($this->options['write'])) {
            $options += $this->options['write'];
        }
        $string = $this->getView()->renderString($string, $options);
        $this->tab->send($string);

        if (empty($options['skipInternalBench'])) {
            $this->finishInternalBench();
        }
    }

    /**
     * Verifies printing data by label condition.
     *
     * @param string $method  Debeetle method name
     * @param string $label  Label
     * @param array $options  Options
     * @return bool
     */
    public function checkLabel($method, $label, array $options)
    {
        if (empty($this->labels[$method])) {
            $this->labels[$method] = [];
        }
        if (empty($this->labels[$method][$label])) {
            $this->labels[$method][$label] = 1;
        } else {
            $this->labels[$method][$label]++;
        }
        return empty($options['labelLimit']) || ($this->labels[$method][$label] <= $options['labelLimit']);
    }

    /**
     * Returns internal benches.
     *
     * @return array
     */
    public function getInternalBenches()
    {
        return $this->bench;
    }

    /**
     * Adds Debeetle path to avoid counting for included files number.
     *
     * @param string $path
     * @return void
     */
    public function addPath($path)
    {
        $path = str_replace("\\", "/", realpath($path) . "/");
        if (!in_array($path, $this->paths)) {
            $this->paths[] = $path;
        }
    }

    /**
     * Returns list of external (not Debeetle) files.
     *
     * @return array
     */
    public function getExternalIncludedFiles()
    {
        return array_values(array_filter(get_included_files(), function ($path) {
            $path = str_replace("\\", "/", dirname($path)) . "/";
            $externalFile = true;
            foreach ($this->paths as $debeetlePath) {
                if (0 === strpos($path, $debeetlePath)) {
                    $externalFile = false;
                    break;
                }
            }
            return $externalFile;
        }));
    }

    /**
     * Returns debug bar data by type.
     *
     * @param string $type  See implementation
     * @param array  $iBench  Internal Debeetle benches
     * @return array
     */
    public function getDataByType($type, array $iBench)
    {
        /**
         * Bench settings
         */
        $bench = $this->settings['bench'];
        $exclude =
            isset($bench[$type]['exclude'])
                ? explode(',', $bench[$type]['exclude'])
                : [];
        $value = null;
        // $secondValue = null;
        switch ($type) {
            case "serverTime":
                $format =
                    isset($bench[$type]['format'])
                        ? $bench[$type]['format']
                        : 'Y/m/d H:i:s O (T)';
                return
                    [
                        date($format, $iBench['initState'][$type])
                    ];

            case "phpVersion":
                $value = sprintf("PHP %s", phpversion());
                break;

            case "pageTotalTime":
                $toExclude = $iBench['initState']['time'];
                if (in_array('debeetle', $exclude)) {
                    $toExclude += $iBench['total']['time'];
                }
                $value = microtime(true) - $toExclude;
                break;

            case "memoryUsage":
                $toExclude = 0;
                if (in_array('scriptInit', $exclude)) {
                    $toExclude += $iBench['initState'][$type];
                }
                if (in_array('debeetle', $exclude)) {
                    $toExclude += $iBench['total'][$type];
                }
                $value = memory_get_usage() - $toExclude;
                break;

            case "peakMemoryUsage":
                $toExclude = 0;
                if (in_array('scriptInit', $exclude)) {
                    $toExclude += $iBench['initState'][$type];
                }
                if (in_array('debeetle', $exclude)) {
                    $toExclude += $iBench['total'][$type];
                }
                $value = memory_get_peak_usage() - $toExclude;
                break;

            case "includedFiles":
                $value = in_array("debeetle", $exclude) ? $iBench['total'][$type] : sizeof(get_included_files());
                break;
        }
        $params = isset($bench[$type]) ? $bench[$type] : [];
        if (isset($params['divider'])) {
            $value = $value / $params['divider'];
            /*
            if ($secondValue) {
                $secondValue = $secondValue / $params['divider'];
            }
            */
        }
        $warning = "";
        if (isset($params['critical']) && $value >= $params['critical']) {
            $warning = "critical";
        } else if (isset($params['warning']) && $value >= $params['warning']) {
            $warning = "warning";
        }
        if (isset($params['format']) && !is_null($value)) {
            $value = (float)sprintf($params['format'], $value);
            /*
            if ($secondValue) {
                $secondValue =
                    number_format(
                        $secondValue,
                        $params['decimalDigits'],
                        '.',
                        ''
                    );
            }
            */
        }
        // $result = array($value . ($secondValue ? ':' . $secondValue : ''));
        $result = [$value];
        if ($warning || isset($params['unit'])) {
            $result[] = $warning;
            if (isset($params['unit'])) {
                $result[] = $params['unit'];
            }
        } else if (
            in_array($type, ["memoryUsage", "peakMemoryUsage"])
        ) {
            $result[] = "";
            $result[] = "bytes";
        }
        return $result;
    }

    public function onShutdown()
    {
        if (isset($this->onShutdown['history'])) {
            $bench = $this->bench;
            foreach ([
                "serverTime",
                "pageTotalTime",
                "memoryUsage",
                "peakMemoryUsage",
            ] as $key) {
                $bench[$key] = $this->getDataByType($key, $bench);
            }
            $data = [
                $bench['serverTime'][0], // 'serverTime' =>
                sprintf(
                    "%s://%s%s",
                    $_SERVER['REQUEST_SCHEME'],
                    $_SERVER['SERVER_NAME'],
                    $_SERVER['REQUEST_URI']
                ), // 'url' =>
                $_SERVER['REQUEST_METHOD'], // 'requestMethod' =>
                sprintf(
                    $this->settings['bench']['pageTotalTime']['format'],
                    $bench['pageTotalTime'][0]
                ), // 'pageTotalTime' =>
                sprintf(
                    $this->settings['bench']['memoryUsage']['format'],
                    $bench['memoryUsage'][0]
                ), // 'memoryUsage' =>
                sprintf(
                    $this->settings['bench']['peakMemoryUsage']['format'],
                    $bench['peakMemoryUsage'][0]
                ), // 'peakMemoryUsage' =>
                $bench['total']['includedFiles'], // 'includedFiles' =>
            ];
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            foreach (['name' => "history", 'storage' => "session"] as $option => $default) {
                $$option = isset($this->settings['history'][$option])
                    ? $this->settings['history'][$option]
                    : $default;
            }
            if ("session" === $storage) {
                session_start();
                $_SESSION[$name] = $data;
            } else {
                setcookie($name, $data);
            }
        }
    }

    /**
     * @param string $type
     * @return void
     */
    public function dropOnShutdown($type)
    {
        unset($this->onShutdown[$type]);
    }

    /**
     * Initializes Debeetle according to the settings.
     *
     * @param array $settings  Array of settings
     * @return void
     */
    protected function init(array $settings)
    {
        $this->bench = [
            'initState' => $settings['initState'],
            'skip' => false,
            'total' => [
                'time' => 0,
                'memoryUsage' => 0,
                'peakMemoryUsage' => 0,
                'includedFiles' => 0,
            ],
            'calls' => [],
        ];
        unset($settings['initState']);
        $this->settings = $settings + ['disabledTabs' => []];
        foreach ($this->settings['skin'] as $skin) {
            $this->addPath(call_user_func([$skin['class'], "getPath"]));
            foreach ($skin['theme'] as $theme) {
                $this->addPath(call_user_func([$theme['class'], "getPath"]));
            }
        }
        foreach ($this->settings['plugin'] as $plugin) {
            $this->addPath(call_user_func([$plugin['class'], "getPath"]));
        }
        $request = HTTPRequest::getInstance();
        $cookie = $request->get($this->settings['cookie']['name'], null, "c");
        $this->skip = empty($cookie);
        $this->tab = new Tree($this->settings);
        if ($this->skip) {
            return;
        }
        $this->addPath(realpath(__DIR__));
        $this->addPath($this->settings['path']['assets']);

        $this->settings['skins'] = [];
        if (
            !in_array("debeetle", $this->settings['disabledTabs']) &&
            !in_array("debeetle|settings", $this->settings['disabledTabs']) &&
            !in_array("settings|settings|panel", $this->settings['disabledTabs'])
        ) {
            $language = $this->settings['defaults']['language'];
            foreach ($this->settings['skin'] as $skinId => $skin) {
                $this->settings['skins'][$skinId]['name'] = $skin['name'][$language];
                $this->settings['skins'][$skinId]['defaultTheme'] = $skin['defaultTheme'];
                $this->settings['skins'][$skinId]['themes'] = [];
                foreach ($skin['theme'] as $themeId => $theme) {
                    $this->settings['skins'][$skinId]['themes'][$themeId] = $theme['name'][$language];
                }
            }
        }

        $this->bench['onLoad'] = [
            'includedFiles' => $this->bench['initState']['includedFiles'],
            'peakMemoryUsage' => 0,
            'memoryUsage' => memory_get_usage() - $this->bench['initState']['memoryUsage'],
        ];
        $this->bench['onLoad']['peakMemoryUsage'] =
            memory_get_peak_usage() - $this->bench['initState']['peakMemoryUsage'];
        $this->bench['onLoad']['time'] = microtime(true) - $this->bench['initState']['time'];
        $this->bench['total'] = $this->bench['onLoad'] + ['qty' => 0];

        if (
            !empty($this->settings['history']) &&
            isset($this->settings['history']['records']) &&
            $this->settings['history']['records'] > 0
        ) {
            $this->onShutdown['history'] = true;
            register_shutdown_function([$this, "onShutdown"]);
        }
    }

    protected function startInternalBench()
    {
        $this->bench['total']['qty']++;
        $this->bench['calls'][] = [
            'memoryUsage' => memory_get_usage(),
            'peakMemoryUsage' => memory_get_peak_usage(),
            'time' => microtime(true),
        ];
    }

    protected function finishInternalBench()
    {
        $current = array_pop($this->bench['calls']);
        if (0 === sizeof($this->bench['calls'])) {
            $this->bench['total']['includedFiles'] = sizeof($this->getExternalIncludedFiles());
            $this->bench['total']['memoryUsage'] += (memory_get_usage() - $current['memoryUsage']);
            $this->bench['total']['peakMemoryUsage'] += memory_get_peak_usage() - $current['peakMemoryUsage'];
            $this->bench['total']['time'] += (microtime(true) - $current['time']);
        }
    }
}
