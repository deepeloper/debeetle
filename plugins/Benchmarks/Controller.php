<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Plugin\Benchmarks;

use deepeloper\Debeetle\Plugin\AbstractController;
use deepeloper\Debeetle\Plugin\Benchmarks\Exception\Exception;

/**
 * Benchmarks plugin.
 */
class Controller extends AbstractController
{
    /**
     * Plugin version
     *
     * Used for building url hash.
     *
     * @see Debeetle_Resource_Public::processRequest()
     */
    const VERSION = "1.0.0";

    protected $onError = "";

    protected $benchmarks = [];

    /**
     * Flag specifying to store delays between calls of checkpoint, memory usage / peak memory usage.
     *
     * @var bool
     */
    protected $storeData = false;

    protected $checkpoints = [];

    /**
     * Returns plugin path.
     *
     * @return string
     */
    public static function getPath()
    {
        return realpath(__DIR__);
    }

    /**
     * Initialize plugin.
     *
     * @return void
     */
    public function init()
    {
        $options = &$this->settings['plugin'][$this->id];
//        var_dump($options);die;###
        if (isset($options['onError'])) {
            $this->onError = $options['onError'];
        }
        if (
            isset($options['checkpoint']['storeData']) &&
            "true" === $options['checkpoint']['storeData']
        ) {
            $this->storeData = true;
        }

        foreach ([
                     'startBenchmark' => "bs",
                     'endBenchmark' => "be",
                     'getBenchmarks' => "getBenchmarks",
                     'checkpoint' => "cp",
                     'getCheckpoints' => "getCheckpoints",
                 ] as $method => $shortcut) {
            $this->debeetle->registerMethod($shortcut, [$this, $method]);
        }
    }

    /**
     * @param string $label
     * @return void
     */
    public function startBenchmark($label)
    {
        if (isset($this->benchmarks[$label])) {
            if (isset($this->benchmarks[$label]['started'])) {
                $this->onError("Starting duplicate benchmark '$label'");
            } else {
                $this->benchmarks[$label]['started'] = microtime(true);
                $this->benchmarks[$label]['count']++;
            }
        } else {
            $this->benchmarks[$label] = [
                'count' => 1,
                'total' => 0,
                'started' => microtime(true),
            ];
        }
    }

    /**
     * @param string $label
     * @return void
     */
    public function endBenchmark($label)
    {
        if (!isset($this->benchmarks[$label])) {
            return $this->onError("Ending unknown benchmark '$label'");
        }
        if (!isset($this->benchmarks[$label]['started'])) {
            return $this->onError("Ending not started benchmark '$label'");
        }
        $this->benchmarks[$label]['total'] += (microtime(true) - $this->benchmarks[$label]['started']);
        unset($this->benchmarks[$label]['started']);
    }

    /**
     * Returns array containing bechmarks labels as keys and array
     * ['count' => (int)count of calls, 'total' => (double)total time].
     *
     * @return array
     */
    public function getBenchmarks()
    {
        return $this->benchmarks;
    }

    /**
     * @param string $label
     * @param array $options  Supports 'storeData' key to force set storeData flag
     * @return void
     */
    public function checkpoint($label, array $options = [])
    {
        $storeData = isset($options['storeData']) ? $options['storeData'] : $this->storeData;
        if (isset($this->checkpoints[$label])) {
            if ($storeData) {
                if (!isset($this->checkpoints[$label]['data'])) {
                    $this->onError("Checkpoint called without previous call");
                }
                $time = microtime(true);
                $this->checkpoints[$label]['data'][sizeof($this->checkpoints[$label]['data']) - 1][0] =
                    $time - $this->checkpoints[$label]['time'];
                $data = [
                    0,
                    memory_get_usage(),
                ];
                if (function_exists("memory_get_peak_usage")) {
                    $data[] = memory_get_peak_usage();
                }
                $this->checkpoints[$label]['data'][] = $data;
                $this->checkpoints[$label]['time'] = $time;
            }
            $this->checkpoints[$label]['count']++;
        } else {
            $this->checkpoints[$label] = ['count' => 1];
            if ($storeData) {
                $this->checkpoints[$label]['time'] = microtime(true);
                $data = [
                    0,
                    memory_get_usage(),
                ];
                if (function_exists("memory_get_peak_usage")) {
                    $data[] = memory_get_peak_usage();
                }
                $this->checkpoints[$label]['data'][] = $data;
            }
        }
    }

    /**
     * Returns array containing checkpoints labels as keys and array
     * ['count' => (int)count of calls, 'data' (if storeData is true) => [
     *   (double)time to the next call,
     *   (int)memory usage,
     *   (int)peak memory usage
     * ]].
     *
     * @return array
     */
    public function getCheckpoints()
    {
        return $this->checkpoints;
    }

    protected function onError($message)
    {
        switch ($this->onError) {
            case "":
                break;

            case "exception":
                throw new Exception($message);

            default:
                trigger_error($message, constant($this->onError));
        }
    }
}
