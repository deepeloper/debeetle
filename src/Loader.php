<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle;

use deepeloper\Debeetle\Exception\Exception;
use deepeloper\Debeetle\Exception\InvalidDomDocumentSchemaException;
use deepeloper\Debeetle\Exception\OutOfRangeException;
use deepeloper\Debeetle\Exception\UnderflowException;
use deepeloper\Debeetle\Plugin\ControllerInterface;
use deepeloper\Lib\XML\Converter;
use DOMDocument;

/**
 * Debeetle service class.
 *
 * Implements config reading and initialization.
 */
class Loader
{
    /**
     * Settings parsed from passed config array and XML config
     *
     * @var array
     * @see Loader::startup()
     */
    protected static $settings;

    /**
     * Logger
     *
     * @var Logger
     * @see Loader::startup()
     */
    protected static $logger;

    /**
     * Entry point.
     *
     * Registers autoload, parses XML config and returns Debeetle_Interface instance.
     *
     * @param  array $startup  Startup struct
     * @param  bool  $makeInstance  Flag specifying to make Debeetle instance
     * @return DebeetleInterface|array|void  Instance or array of settings
     * @throws Exception
     */
    public static function startup(
        array $startup,
        $makeInstance = true
    )
    {
        @ini_set("unserialize_callback_func", "spl_autoload_call");

        $settings = strpos($startup['config'], '.json')
            ? static::loadJSONConfig($startup)
            : static::loadXMLConfig($startup);
        $settings = static::mergeConfigs($settings);
        if (!isset($settings['defaults'])) {
            return;
        }

        if (!isset($settings['delayBeforeShowInBrowser'])) {
            $settings['delayBeforeShowInBrowser'] = 0;
        }

/*
        if (!empty($settings['logger'])) {
            self::$logger = new Logger($settings['logger']);
        }
*/

        $settings['skin'] = array_filter($settings['skin'], function (array $skin) {
            return !empty($skin['use']) && class_exists($skin['class']);
        });
        foreach ($settings['skin'] as $skinId => $skin) {
            $themes = $skin['theme'];
            $themes = array_filter($themes, function (array $theme) {
                return !empty($theme['use']) && class_exists($theme['class']);
            });
            $settings['skin'][$skinId]['theme'] = $themes;
        }
        unset($themes, $skin, $skinId);

        if (isset($settings['plugin'])) {
            $settings['plugin'] = array_filter($settings['plugin'], function (array $plugin) {
                return !empty($plugin['use']) && class_exists($plugin['class']);
            });
        } else {
            $settings['plugin'] = [];
        }

        $settings['locales'] = [];
        $language = $settings['defaults']['language'];
        $locales = array_unique([$language, "en"]);
        $skinPath = call_user_func([$settings['skin'][$settings['defaults']['skin']]['class'], "getPath"]);
        foreach ($locales as $locale) {
            $path = "$skinPath/locales/$locale.php";
            if (file_exists($path)) {
                $settings['locales'] = require $path;
                break;
            }
        }
        foreach ($settings['plugin'] as $plugin) {
            $pluginPath = call_user_func([$plugin['class'], "getPath"]);
            if (!empty($plugin['locale'])) {
                foreach ($locales as $locale) {
                    $path = "$pluginPath/locales/$locale.php";
                    if (file_exists($path)) {
                        $settings['locales'] += require $path;
                        break;
                    }
                }
            }
        }

        self::$settings = $settings;

        if ($makeInstance) {
            // Make Debeetle instance
            if (empty($settings)) {
                $instance = new Stub;
            } else {
                $instance = new Debeetle($settings + $startup);
                // Load plugins
                foreach ($settings['plugin'] as $id => $plugin) {
                    /**
                     * @var ControllerInterface $plugin
                     */
                    $plugin = new $plugin['class']();
                    $plugin->setInstance($instance, $id);
                    $plugin->init();
                }
                d::setInstance($instance);
            }
            self::$settings = $settings;
            return $instance;
        }
        return self::$settings;
    }

    /**
     * Returns settings parsed from passed config array and XML config
     *
     * @return array
     */
    public static function getSettings()
    {
        return self::$settings;
    }

    public static function onError($message = "", $exception = null)
    {
        if (!empty(self::$settings['debug'])) {
            if ("exception" === self::$settings['debug']) {
                throw new $exception($message);
            } else {
                trigger_error($message, constant(self::$settings['debug']));
            }
        }
    }

    /**
     * Returns settings parsed from passed config array and XML config
     *
     * @return Logger
     * @todo Do we need it?
     */
//    public static function getLogger()
//    {
//        return self::$logger;
//    }

    protected static function loadJSONConfig(array $startup)
    {
        $json = preg_replace(
            ["~/\s*\*.*?\*/\s*?~s", "~,\s+}~", "~,\s+]~"],
            ["", "}", "]"],
            file_get_contents($startup['config'])
        );
        $settings = json_decode($json, true);
        if (null === $settings) {
            $settings = [];
        }

        return $settings;
    }

    /**
     * @param array $startup
     * @return array|array[]
     * @throws InvalidDomDocumentSchemaException
     */
    protected static function loadXMLConfig(array $startup)
    {
        $xmlPath = $startup['config'];
        $xsdPath = dirname($startup['config']) . "/debeetle.xsd";
        $settings = [];
        try {
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->load($xmlPath);
            if (!$doc->schemaValidate($xsdPath)) {
                $messages = [];
                $errors = libxml_get_errors();
                foreach ($errors as $error) {
                    $message = "";
                    switch ($error->level) {
                        case LIBXML_ERR_WARNING:
                            $message = "[ WARNING $error->code ] ";
                            break;
                        case LIBXML_ERR_ERROR:
                            $message = "[ ERROR $error->code ] ";
                            break;
                        case LIBXML_ERR_FATAL:
                            $message = "[ FATAL ERROR $error->code ] ";
                            break;
                    }
                    $message .= trim($error->message, " \t\n\r\0\x0B.");
                    if ($error->file) {
                        $message .= " in '$error->file'";
                    }
                    $message .= " on line $error->line";
                    $messages[] = $message;
                }
                self::onError(
                    "DOMDocument::schemaValidate() errors:<br>\n" . implode("<br>\n", $messages),
                    "InvalidDomDocumentSchemaException"
                );
            }

            $converter = new Converter();

            $xsd = file_get_contents($xsdPath);
            if (version_compare(phpversion(), "8.1", ">=")) {
                $xsd = [$xsd];
            }
            $settings = $converter->parse(
                file_get_contents($xmlPath),
                $xsd,
                [
                    Converter::COLLAPSE_ATTRIBUTES => true,
                    Converter::COLLAPSE_CHILDREN => true,
                    Converter::COLLAPSE_ARRAYS => [
                        'exclusions' => [
                            "debeetle/config",
                            "debeetle/config/defaults/opacity/selector",
                            "debeetle/config/defaults/zoom/selector",
                            "debeetle/config/skin",
                            "debeetle/config/skin/theme",
                            "debeetle/config/limit",
                        ],
                    ],
                ]
            );

            $settings = $settings + $startup + ['config'=> [], 'plugin' => []];

            self::replaceKeysWithElementValue($settings['config'], "name");

            foreach ($settings['config'] as $configIndex => $config) {
                if (isset($config['plugin'])) {
                    foreach ($settings['config'][$configIndex]['plugin'] as $pluginIndex => $plugin) {
                        if (isset($plugin['method'])) {
                            // @todo Commit different code for different PHP versions.
                            if (version_compare(PHP_VERSION, "7.3", ">=")) {
                                $key = array_key_first($plugin['method']);
                            } else {
                                @list($key, ) = each($plugin['method']);
                            }
                            if (!is_int($key)) {
                                $settings['config'][$configIndex]['plugin'][$pluginIndex]['method'] =
                                    [$plugin['method']];
                            }
                            self::replaceKeysWithElementValue(
                                $settings['config'][$configIndex]['plugin'][$pluginIndex]['method'],
                                "name"
                            );
                        }
                    }
                    self::replaceKeysWithElementValue($settings['config'][$configIndex]['plugin'], "id");
                }
                if (isset($config['skin'])) {
                    foreach ($settings['config'][$configIndex]['skin'] as $skinIndex => $skin) {
                        if (isset($skin['theme'])) {
                            self::replaceKeysWithElementValue(
                                $settings['config'][$configIndex]['skin'][$skinIndex]['theme'],
                                "id"
                            );
                        }
                    }
                    self::replaceKeysWithElementValue($settings['config'][$configIndex]['skin'], "id");
                }
            }

        } catch (Exception $exception) {
            if (!empty($startup['developerMode'])) {
                self::onError($exception->getMessage(), et_class($exception));
            }
        }

        return $settings;
    }

    /**
     * @param array $settings
     * @return array
     * @throws OutOfRangeException
     * @throws UnderflowException
     */
    protected static function mergeConfigs(array $settings)
    {
        $limitAttributes = [
            'source' => true,
            'key' => true,
            'value' => false,
        ];
        $limitSourceToGlobals = [
            'SERVER' => &$_SERVER,
            'COOKIE' => &$_COOKIE,
            'SESSION' => &$_SESSION,
            'REQUEST' => &$_REQUEST,
            'GET' => &$_GET,
            'POST' => &$_POST,
        ];
        $suitableLimitsFound = false;
        $result = [];
        foreach ($settings['config'] as $id => $config) {
            if (empty($config['use'])) {
                continue;
            }
            unset($config['use']);
            $limits = isset($config['limit']) ? $config['limit'] : [];
            unset($config['limit']);
            $limitIsSuitable = false;
            foreach ($limits as $limit) {
                $limitIsSuitable = true;
                $currentLimit = [];
                // Validate limit attributes
                foreach ($limitAttributes as $name => $obligatoriness) {
                    if (array_key_exists($name, $limit)) {
                        $currentLimit[$name] = $limit[$name];
                    } else if ($obligatoriness) {
                        self::onError(
                            "Missing obligatory element 'config/limit/$name'",
                            "UnderflowException"
                        );
                    }
                }
                // Check limit attributes
                if (isset($limitSourceToGlobals[$currentLimit['source']])) {
                    $source = $limitSourceToGlobals[$currentLimit['source']];
                    if (array_key_exists($currentLimit['key'], $source)) {
                        if (array_key_exists("value", $limit)) {
                            $value = $limit['value'];
                            $sourceValue = $source[$limit['key']];
                            if ("/" !== substr($value, 0, 1)) {
                                $limitIsSuitable = $value === $sourceValue;
                            } else {
                                $limitIsSuitable = preg_match($value, $sourceValue);
                            }
                        }
                    } else {
                        $limitIsSuitable = false;
                    }
                    if (!$limitIsSuitable) {
                        break; // foreach ($limits as $limit)
                    }
                } else {
                    self::onError(
                        "Invalid element 'config/limit/$name'",
                        "OutOfRangeException"
                    );
                }
            }
            if (empty($limits)) {
                $result['loaded'][] = $id;
                $result = array_replace_recursive($result, $config);
            } else {
                if ($limitIsSuitable) {
                    $suitableLimitsFound = true;
                    $result['loaded'][] = $id;
                    $result = array_replace_recursive($result, $config);
                }
            }
        }
        if ($suitableLimitsFound) {
            if (!empty($result['plugin'])) {
                foreach ($result['plugin'] as $plugin) {
                    if (empty($plugin['method'])) {
                        continue;
                    }
                    foreach ($plugin['method'] as $name => $method) {
                        $result['defaults']['options'][$name] = $method;
                    }
                }
            }
        } else {
            $result = [];
        }
        return $result;
    }

    /**
     * @param array $array
     * @param string $elementName
     * @return void
     */
    protected static function replaceKeysWithElementValue(array &$array, $elementName)
    {
        $keys = array_map(function ($record) use ($elementName) {
            return $record[$elementName];
        }, $array);
        array_walk($array, function (&$record) use ($elementName) {
            unset($record[$elementName]);
        });
        $array = array_combine($keys, $array);
    }
}
