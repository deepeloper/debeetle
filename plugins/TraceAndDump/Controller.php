<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Plugin\TraceAndDump;

use deepeloper\Debeetle\Plugin\AbstractController;
use InvalidArgumentException;

/**
 * Hit And Run, oh no, Trace And Dump plugin.
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

    /**
     * Templates
     *
     * @var array
     */
    protected $templates = [
        'dump_entity_opener' => '<pre>',
        'dump_entity_closer' => '</pre>',
    ];

    /**
     * @var string
     */
    protected $templatePath;

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
        $options = &$this->settings['defaults']['options']['dump'];
        $this->setOptionsType($options, ["maxStringLength", "maxNesting", "maxCount"], "int");
        $this->patchBooleanOptions($options, ["expand", "expandEntities"]);
        $options = &$this->settings['defaults']['options']['trace'];
        $this->patchBooleanOptions($options, ["expand", "displayArgs", "expandArgs"]);

        $this->templatePath = self::getPath() . "/templates/krumo.php";
        foreach (['trace', 'dump', 'vd', 'pr', 've', 'zv'] as $method) {
            $this->debeetle->registerMethod($method, [$this, $method], 2);
        }
        $this->debeetle->registerMethod('du', [$this, 'dump'], 2);
    }

    /**
     * Display settings
     *
     * @return void
     */
    public function displaySettings()
    {
        $options = isset($this->settings['defaults']['options']['trace'])
            ? $this->settings['defaults']['options']['trace']
            : [];
        $this->debeetle->tab("plugins|traceAndDump");
        ob_start();
        $part = "settings";
        require $this->templatePath;
        $this->debeetle->write(
            ob_get_clean(),
            [
                'htmlEntities' => false,
                'nl2br'        => false,
                'skipEncoding' => true
            ]
        );
    }

    /**
     * Writes trace to debug output.
     *
     * @param ?array $trace  Array containing trace
     *                       (i.e. debug_backtrace() result)
     * @param ?string $label  Label
     * @param ?array $options  Reserved array for functionality enhancement
     * @return void
     */
    public function trace(array $trace = null, $label = '', array $options = [])
    {
        if ($label !== "" && !$this->debeetle->checkLabel("trace", $label, $options)) {
            return;
        }
        if (isset($this->settings['defaults']['options']['trace'])) {
            $options += $this->settings['defaults']['options']['trace'];
        }
//        echo "<pre>"; var_dump($this->debeetle->getTrace()); echo "</pre>";###
//        if (null === $this->debeetle->getTrace()) {
//            $this->debeetle->setTrace(2);
//        }
        $this->debeetle->write(
            $this->renderTrace(
                $trace,
                $options
            ),
            [
                'htmlEntities'      => false,
                'nl2br'             => false,
                'skipInternalBench' => true,
                'skipEncoding'      => true
            ] + $options
        );
    }

    /**
     * Dump entity using var_dump(), print_r(), var_export() and etc.
     *
     * Example:
     * <code>
     * // Dumps request arrays to appropriate tabs
     * d::dump($_REQUEST, 'Request|All');
     * d::dump($_GET, 'Request|GET');
     * d::dump($_POST, 'Request|POST');
     * d::dump($_COOKIE, 'Request|COOKIE');
     * </code>
     *
     * @param  mixed  $entity   Entity to dump
     * @param  string $label    Entity label
     * @param  array  $options  Associative array of options,
     *                          key 'method' can contain
     *                          'var_dump' | 'print_r' | 'var_export' |
     *                          'debug_zval_dump', also you can use this
     *                          argument for functionality enhancement
     * @return void
     */
    public function dump($entity, $label = "", array $options = [])
    {
        if (
            $label !== '' &&
            !$this->debeetle->checkLabel('dump', $label, $options)
        ) {
            return;
        }
        if ($label !== '') {
            $options['label'] = $label;
        }
        if (isset($this->settings['defaults']['options']['dump'])) {
            $options += $this->settings['defaults']['options']['dump'];
        }
        $this->debeetle->write(
            $this->renderEntity(
                $entity,
                $options
            ),
            [
                'htmlEntities'      => false,
                'nl2br'             => false,
                'skipInternalBench' => true,
                'skipEncoding'      => true
            ] + $options
        );
    }

    /**
     * Dump entity using var_dump
     *
     * Example:
     * <code>
     * // Dumps request arrays to appropriate tabs
     * d::t('Request|All');
     * d::vd($_REQUEST);
     * d::t('Request|GET');
     * d::vd($_GET);
     * d::t('Request|POST');
     * d::vd($_POST);
     * d::t('Request|COOKIE');
     * d::vd($_COOKIE);
     * </code>
     *
     * @param  mixed  $entity   Entity to dump
     * @param  string $label    Entity label
     * @param  array  $options  Associative array of options
     * @return void
     * @see    Debeetle_TraceAndDump::dump()
     */
    public function vd($entity, $label = "", array $options = [])
    {
        $options['method'] = "var_dump";
        $options['label'] = $label;
        $this->_dump($entity, $options);
    }

    /**
     * Dump entity using print_r
     *
     * Example:
     * <code>
     * // Dumps request arrays to appropriate tabs
     * d::t('Request|All');
     * d::pr($_REQUEST);
     * d::t('Request|GET');
     * d::pr($_GET);
     * d::t('Request|POST');
     * d::pr($_POST);
     * d::t('Request|COOKIE');
     * d::pr($_COOKIE);
     * </code>
     *
     * @param  mixed  $entity   Entity to dump
     * @param  string $label    Entity label
     * @param  array  $options  Associative array of options
     * @return void
     * @see    Debeetle_TraceAndDump::dump()
     */
    public function pr($entity, $label = "", array $options = [])
    {
        $options['method'] = "print_r";
        $options['label'] = $label;
        $this->_dump($entity, $options);
    }

    /**
     * Dump entity using var_export
     *
     * Example:
     * <code>
     * // Exports variable to appropriate tab
     * $variable = array(1, 2, 3, 'key' => 'value');
     * d::t('PHP represented variables');
     * d::ve($variable);
     * </code>
     *
     * @param  mixed  $entity   Entity to dump
     * @param  string $label    Entity label
     * @param  array  $options  Associative array of options
     * @return void
     * @see    Debeetle_TraceAndDump::dump()
     */
    public function ve($entity, $label = "", array $options = [])
    {
        $options['method'] = "var_export";
        $options['label'] = $label;
        $this->_dump($entity, $options);
    }

    /**
     * Dump entity using debug_zval_dump
     *
     * Example:
     *
     * @param  mixed  $entity   Entity to dump
     * @param  string $label    Entity label
     * @param  array  $options  Associative array of options
     * @return void
     * @see    Debeetle_TraceAndDump::dump()
     */
    public function zv($entity, $label = "", array $options = [])
    {
        $options['method'] = "debug_zval_dump";
        $options['label'] = $label;
        $this->_dump($entity, $options);
    }

    /**
     * Render entity
     *
     * @param  mixed  $entity   Entity
     * @param  array  $options  Reserved array for functionality enhancement
     * @return string
     */
    protected function renderEntity($entity, array $options = [])
    {
        $method = empty($options['method']) ? [$this, '_dump'] : $options['method'];
        ob_start();
        if (in_array($method, ['var_dump', 'print_r', 'var_export', 'debug_zval_dump'])) {
            $dump = $this->templates['dump_entity_opener'];
            $method($entity);
            $dump .=
                $this->debeetle->getView()->renderString(ob_get_clean(), ['nl2br' => false] + $options) .
                $this->templates['dump_entity_closer'];
        } else {
            call_user_func($method, $entity, $options);
            $dump = ob_get_clean();
        }
        return empty($options['skipFieldset']) ? $this->getFieldset("dump", $dump, $options) : $dump;
    }

    /**
     * Renders trace.
     *
     * @param ?array $trace  Trace array
     * @param ?array $options  Reserved array for functionality enhancement
     * @return string
     * @see Debeetle::trace()
     * @todo Several transforms?
     */
    protected function renderTrace(array $trace = null, array $options = [])
    {
        if (is_null($trace)) {
            if (empty($options['displayArgs'])) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            } else {
                $trace = debug_backtrace();
            }
            $stored = $this->debeetle->getTrace();
            $start = 0;
            $found = false;
            foreach ($trace as $start => $row) {
                if (
                    isset($row['file']) && $row['file'] === $stored['file'] &&
                    isset($row['line']) && $row['line'] === $stored['line']
                ) {
                    $found = true;
                } else {
                    if ($found) {
                        $start--;
                        break;
                    }
                }
            }
        } else {
            $start = isset($options['traceOffset']) ? $options['traceOffset'] : 0;
        }

        $dumpOptions =
            isset($this->settings['defaults']['options']['dump'])
            ? $this->settings['defaults']['options']['dump']
            : [];
        $locales = $this->settings['locales'];

        ob_start();
        $part = "header:trace";
        require $this->templatePath;
        $content = ob_get_clean();
        $even = false;
        for ($i = $start, $q = sizeof($trace); $i < $q ; $i++) {
            $location = str_replace(
                DIRECTORY_SEPARATOR,
                '/',
                (isset($trace[$i]['file']) ? $trace[$i]['file'] : '') .
                (isset($trace[$i]['line']) ? ': ' . $trace[$i]['line'] : '') .
                '&nbsp;'
            );
            if(
                isset($this->settings['path']['root']) &&
                mb_strpos($location, $this->settings['path']['root']) === 0
            ){
                $location = mb_substr(
                    $location,
                    mb_strlen($this->settings['path']['root'])
                );
            }

            $argsQty =
                empty($trace[$i]['args']) ? '' : sizeof($trace[$i]['args']);

            $caller =
                isset($trace[$i]['class'])
                ? $trace[$i]['class'] . $trace[$i]['type'] .
                  $trace[$i]['function'] . '(' . $argsQty . ')'
                : (
                    isset($trace[$i]['function'])
                    ? $trace[$i]['function'] . '(' . $argsQty . ')'
                    : '&nbsp;'
                );

            $args =
                $argsQty && !empty($options['displayArgs'])
                ? $this->renderEntity(
                    $trace[$i]['args'],
                    [
                        'skipFieldset' => true,
                        'traceArgs'    => true
                    ] + $options + $dumpOptions
                )
                : '';

            ob_start();
            $part = 'row:trace';
            require $this->templatePath;
            $content .= ob_get_clean();

            $even = !$even;
        } // end for()
        ob_start();
        $part = 'footer:trace';
        require $this->templatePath;
        $content .= ob_get_clean();

        return $this->getFieldset('trace', $content, $options);
    }

    /**
     * Returns fieldset HTML
     *
     * @param  string $source   Source ('dump'|'trace')
     * @param  string $content  Fieldset content
     * @param  array  $options  Options
     *
     * @return string
     */
    protected function getFieldset($source, $content, array $options)
    {
        ob_start();
        $trace = "";
        if (empty($options['hideTrace'])) {
            $trace = $this->debeetle->getTrace();
            $trace =
                (isset($trace['file']) ? str_replace(DIRECTORY_SEPARATOR, '/', $trace['file']) : '') .
                (isset($trace['line']) ? ': ' . $trace['line'] : '');
            if (
                isset($this->settings['path']['root']) &&
                mb_strpos($trace, $this->settings['path']['root']) === 0
            ) {
                $trace = mb_substr($trace, mb_strlen($this->settings['path']['root']));
            }
        }
        $label = isset($options['label']) ? $this->debeetle->getView()->renderString($options['label'], $options) : '';
        $part = "fieldset:$source";
        require $this->templatePath;
        return ob_get_clean();
    }

    /**
     * Render trace argument
     *
     * @param  string $arg      Trace argument
     * @param  array  $options  Options
     * @return string
     * @todo   Use or wipe
     */
    protected function renderTraceArgument($arg, array $options)
    {
        if (is_object($arg)) {
            $arg = 'Object (' . get_class($arg) . ')';
        }elseif(is_array($arg)) {
            $arg = 'Array (' . sizeof($arg) . ')';
        }elseif(is_resource($arg)) {
            ob_start();
            var_dump($arg);
            $arg = $this->debeetle->getView()->renderString(ob_get_clean(), $options);
        }else{
            $arg = (string)$arg;
            $length = mb_strlen($arg, $options['encoding']);
            if ($length > $options['arg_max_str_len']) {
                $arg = '"' . mb_substr($arg, 0, $options['arg_max_str_len'], $options['encoding']) . '" ...';
            } else {
                $arg = '"' . $arg . '"';
            }
            $arg = $this->debeetle->getView()->renderString('string (' . $length . ') ' . $arg, $options);
        }
        return $arg;
    }

    /**
     * Dumps entity.
     *
     * @param  mixed  $entity   Entity
     * @param  array  $options  Options
     * @param  int    $nesting  Nesting level
     * @param  string $caption  Caption
     * @param  string $title    Row title
     * @return void
     */
    protected function _dump($entity, array $options, $nesting = 0, $caption = '', $title = '')
    {
        if (!$nesting) {
            $part = "header:dump";
            require $this->templatePath;
        }
        $type = $this->getType($entity);
        switch ($type) {
            case 'array':
            case 'object':
            case 'resource':
            case 'string':
                $part = 'entity:' . $type;
                require $this->templatePath;
                break;
            default:
                if ("bool" === $type) {
                    $entity = $entity ? " true" : " false";
                } elseif ("null" === $type) {
                    $entity = "null";
                }
                $part = "entity:scalar";
                require $this->templatePath;
                break;
        }
        if (!$nesting) {
            $part = 'footer:dump';
            require $this->templatePath;

            // flee the hive
            $marker = $this->krumoGetMarker();
            $dummy = null;
            $hive = $this->krumoHive($dummy);
            if ($hive) {
                foreach ($hive as $i => $bee) {
                    if (is_object($bee)) {
                        unset($bee->$marker);
                    } else {
                        unset($hive[$i][$marker]);
                    }
                }
            }
        }
    }

    protected function krumoRenderVars($entity, $options, $nesting)
    {
        // Test for references in order to prevent endless recursion loops
        $marker = $this->krumoGetMarker();
        $isObject = is_object($entity);
        if ($isObject ? isset($entity->$marker) : isset($entity[$marker])) {
            $part = 'recursion';
            require $this->templatePath;
            return;
        }

        // stain it
        $this->krumoHive($entity);

        // render it
        $part = 'node:start';
        require $this->templatePath;

	$keys = $isObject ? array_keys(get_object_vars($entity)) : array_keys($entity);

	// Iterate
        if (
            empty($options['maxNesting']) ||
            $nesting < $options['maxNesting']
        ) {
            $index = 1;
            foreach($keys as $index => $k) {
                if ($k === $marker) {
                    // skip marker
                    continue;
                }
                if (
                    empty($options['maxCount']) ||
                    $index < $options['maxCount']
                ) {
                    $index++;
                    // get real value
                    if ($isObject){
                        $v = &$entity->$k;
                    } else {
                        $v = &$entity[$k];
                    }
                    $this->_dump($v, $options, $nesting + 1, $k);
                } else {
                    echo "... (count limit ", $options['maxCount'], " )";
                    break;
                }
            }
        } else {
            echo "... (nesting limit $nesting)";
        }
        $part = 'node:end';
        require $this->templatePath;
    }

    /**
     * Returns the marked used to stain arrays and objects
     * in order to detect recursions.
     *
     * @return string
     */
    protected function krumoGetMarker()
    {
        static $marker = null;

        if (is_null($marker)) {
            $marker = uniqid('krumo');
        }
        return $marker;
    }

    /**
     * Adds a variable to the hive of arrays and objects which
     * are tracked for whether they have recursive entries.
     *
     * @param  mixed &$bee  Either array or object, not a scalar vale
     * @return array  Array of all the bees
     */
    protected function krumoHive(&$bee)
    {
        static $_ = [];

        // new bee ?
        if (!is_null($bee)) {
            // stain it
            $marker = $this->krumoGetMarker();
            if (is_object($bee)) {
                if (!isset($bee->$marker)) {
                    @$bee->$marker = 0;
                }
                $bee->$marker++;
            } else {
                if (!isset($bee[$marker])) {
                    $bee[$marker] = 0;
                }
                $bee[$marker]++;
            }
            $_[0][] = &$bee;
        }

        // return all bees
        if (isset($_[0])) {
            $result = &$_[0];
        } else {
            $result = null;
        }
        return $result;
    }

    /**
     * Returns entity type.
     *
     * @param  mixed $entity  Entity
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getType($entity)
    {
        if (is_int($entity)) {
            $type = 'int';
        } else if (is_string($entity)) {
            $type = 'string';
        } else if (is_array($entity)) {
            $type = 'array';
        } else if (is_object($entity)) {
            $type = 'object';
        } else if (is_bool($entity)) {
            $type = 'bool';
        } else if (is_float($entity)) {
            $type = 'double';
        } else if (is_resource($entity)) {
            $type = 'resource';
        } else if (is_null($entity)) {
            $type = 'null';
        } else {
            throw new InvalidArgumentException(
                "Unknown entity type '" . gettype($entity) ."'"
            );
        }
        return $type;
    }
}