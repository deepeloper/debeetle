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

    protected $exclude = [];

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

        if (isset($options['method']['startBenchmark']['exclude'])) {
            foreach (["time", "memoryUsage", "peakMemoryUsage"] as $type) {
                $this->exclude[$type] =
                    isset($options['method']['startBenchmark']['exclude'][$type])
                        ? explode(',', $options['method']['startBenchmark']['exclude'][$type])
                        : [];
            }
        }

        foreach ([
                     'startBenchmark' => ["bs"],
                     'endBenchmark' => ["be"],
                     'getBenchmarks' => ["getBenchmarks"],
                     'checkpoint' => ["cp", 1],
                     'getCheckpoints' => ["getCheckpoints"],
                 ] as $method => $shortcut) {
            $optionsArgIndex = isset($shortcut[1]) ? $shortcut[1] : null;
            $this->debeetle->registerMethod($shortcut[0], [$this, $method], $optionsArgIndex);
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
        if (in_array("debeetle", $this->exclude['time'])) {
            $internal = $this->debeetle->getInternalBenches();
            $this->benchmarks[$label]['internalTime'] = $internal['total']['time'];
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
        if (in_array("debeetle", $this->exclude['time'])) {
            $internal = $this->debeetle->getInternalBenches();
            $this->benchmarks[$label]['total'] -=
                ($internal['total']['time'] - $this->benchmarks[$label]['internalTime']);
        }
        unset($this->benchmarks[$label]['started'], $this->benchmarks[$label]['internalTime']);
    }

    /**
     * Returns array containing bechmarks labels as keys and array
     * ['count' => (int) count of calls, 'total' => (double) total time].
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
        $internal = $this->debeetle->getInternalBenches();

        if (isset($this->checkpoints[$label])) {
            if ($storeData) {
                if (!isset($this->checkpoints[$label]['data'])) {
                    $this->onError("Checkpoint called without previous call");
                }
                $time = microtime(true);
                $prevIndex = sizeof($this->checkpoints[$label]['data']) - 1;
                $data = $this->checkpoints[$label]['data'][$prevIndex];
                $data['timeToNext'] = $time - $this->checkpoints[$label]['time'];
                if (in_array("debeetle", $this->exclude['time'])) {
                    $data['timeToNext'] -= ($internal['total']['time'] - $data['internalTime']);
                    unset($data['internalTime']);
                }
                $this->checkpoints[$label]['data'][$prevIndex] = $data;
                $this->addCheckpintData($label, $internal);
                $this->checkpoints[$label]['time'] = $time;
            }
            $this->checkpoints[$label]['count']++;
        } else {
            $this->checkpoints[$label] = ['count' => 1];
            if ($storeData) {
                $this->checkpoints[$label]['time'] = microtime(true);
                $this->addCheckpintData($label, $internal);
            }
        }
    }

    /**
     * Returns array containing checkpoints labels as keys and array
     * ['count' => (int) count of calls, 'data' (if storeData is true) => [
     *   (double) time to the next call,
     *   (int) memory usage,
     *   (int) peak memory usage
     * ]].
     *
     * @return array
     */
    public function getCheckpoints()
    {
        return $this->checkpoints;
    }

    protected function addCheckpintData($label, array $internal)
    {
        $data = [
            'timeToNext' => 0,
            'memoryUsage' => memory_get_usage(),
            'peakMemoryUsage' => memory_get_peak_usage(),
        ];
        if (in_array("debeetle", $this->exclude['time'])) {
            $data['internalTime'] = $internal['total']['time'];
        }
        foreach (["memoryUsage", "peakMemoryUsage"] as $type) {
            foreach ([
                         'scriptInit' => "initState",
                         'debeetle' => "total",
                     ] as $exclusion => $target) {
                if (in_array($exclusion, $this->exclude[$type])) {
                    $data[$type] -= $internal[$target]['memoryUsage'];
                }
            }
        }
        $this->checkpoints[$label]['data'][] = $data;
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
