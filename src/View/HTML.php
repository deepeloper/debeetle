<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\View;

use deepeloper\Debeetle\d;
use deepeloper\Debeetle\Tree\Tree;
use deepeloper\Debeetle\Tree\Node;
use DirectoryIterator;

/**
 * @todo Describe.
 */
class HTML implements ViewInterface
{
    const VERSION = "1.4.000";

    /**
     * Settings
     *
     * @var array
     */
    protected $settings;

    /**
     * @var Tree
     */
    protected $tab;

    /**
     * View scope
     *
     * @var array
     */
    protected $scope = [
        'version' => self::VERSION,
    ];

    /**
     * Constructor
     *
     * @param array $settings  Array containing debug settings
     */
    public function __construct(array $settings = [])
    {
        $this->scope =
                $settings['defaults'] +
                $this->scope;
        $settings['eol'] =
            isset($settings['eol'])
                ? str_replace(['\n', '\r'], ["\n", "\r"], $settings['eol'])
                : PHP_EOL;

        $this->settings = $settings;
        // Disable caching of JavaScript, CSS, templates.
        if (!empty($this->settings['disableCaching'])) {
            $this->scope['version'] .= "." . mt_rand();
        }
    }

    public function addLocales(array $locales, $override = false)
    {
        $this->tab->addLocales($locales, $override);
    }

    /**
     * Add scope
     *
     * @param  array $scope
     * @return HTML
     */
    public function addScope(array $scope)
    {
        $this->scope = array_merge_recursive($this->scope , $scope);
        return $this;
    }

    /**
     * Sets tab object.
     *
     * @param ?Tree $tab
     * @return void
     */
    public function setTab(?Tree $tab = null)
    {
        $this->tab = $tab;
    }

    /**
     * Returns code initializing debugger.
     *
     * See {@link ViewInterface::get()} usage example.
     *
     * @return string  Appropriate HTML code
     */
    public function get()
    {
        if (is_null($this->tab)) {
            // no output
            return "";
        }
        $isLaunched = d::getInstance()->isLaunched();
        if ($isLaunched) {
            d::getInstance()->dropOnShutdown("history");
        }
        $scope = $this->scope;
        unset($this->scope);
        foreach (['version', 'skin', 'theme'] as $var) {
            $scope[$var] = rawurlencode($scope[$var]);
        }
        if ($isLaunched && $this->settings['developerMode']) {
            $settings = $this->settings;
            unset($settings['limits']);
            d::t("debeetle|loadedConfig");
            d::dump($settings, "", ['hideTrace' => true]);
        }

        ob_start();
        if ($isLaunched) {
            $dir = new DirectoryIterator(
                $this->settings['path']['assets'] . "/tabs"
            );
            $tabSettingsContent = "";
            foreach ($dir as $file) {
                $name = $file->getBasename('.php');
                if (
                    $file->isDot() ||
                    $file->isDir() ||
                    !preg_match('/^\d+\./', $name)) {
                    continue;
                }
                $tab = preg_replace(
                    ['/^\d+\./', '/-/'],
                    ['', '|'],
                    $name
                );

                if (
                    (
                        !$this->settings['developerMode'] &&
                        in_array($tab, ["debeetle|loadedConfig", "debeetle|resourceUsage"])
                    ) ||
                    in_array($tab, $this->settings['disabledTabs'])
                ) {
                    continue;
                }

                ob_start();
                $locales = $this->settings['locales'];
                require_once $this->settings['path']['assets'] . "/tabs/$file";
                $content = ob_get_clean();
                if ($content) {
                    d::t($tab);
                    d::w(
                        $content,
                        ['htmlEntities' => false, 'nl2br' => false]
                    );
                }
                unset($content);
                if ("debeetle|settings" === $tab) {
                    d::getInstance()->callPluginMethod("displaySettings");
                }
            }

            $displayHistory = !empty($this->settings['history']) &&
                isset($this->settings['history']['records']) &&
                $this->settings['history']['records'] > 0;

            if ($displayHistory) {
                d::t("debeetle|history", null, ["before:debeetle|settings"]);
            }
        }

        // Used in "init.php".
        $tabs = $this->getTab($this->tab->get());
        $captions = json_encode($this->tab->getCaptions(), JSON_UNESCAPED_UNICODE);

        // Used in "init.php".
        $data = [
            'version' => urldecode($scope['version']),
            'cookie' => $this->settings['cookie'],
            'delayBeforeShowInBrowser' => $this->settings['delayBeforeShowInBrowser'],
            'path' => ['script' => $this->settings['path']['script']],
            'defaults' => $this->settings['defaults'],
        ];

        if(!empty($this->settings['skins'])) {
            $data['skins'] = $this->settings['skins'];
        }
        if($isLaunched && $displayHistory) {
            $data['history'] = $this->settings['history'];
            foreach (['name' => "history", 'storage' => "session"] as $option => $default) {
                $$option = isset($this->settings['history'][$option])
                    ? $this->settings['history'][$option]
                    : $default;
            }
            if ("session" === $storage) {
                if (isset($_SESSION[$name])) {
                    $stored = $_SESSION[$name];
                    unset($_SESSION[$name]);
                }
            } else {
                if (isset($_COOKIE[$name])) {
                    $stored = $_COOKIE[$name];
                    unset($_COOKIE[$name]);
                    setcookie($_COOKIE[$name], "", -1, "/");
                }
            }
            if (isset($stored)) {
                $data['history']['storage'] = $stored;
            }
        }

        $bench = d::getInstance()->getInternalBenches();
        $data['placeholder'] = 0;
        $hash = "";
        foreach ($this->settings['plugin'] as $id => $plugin) {
            $class = $plugin['class'];
            $hash .= "$id|$class::VERSION|";
        }
        $data['hash'] = md5($hash);
        $data['visibleVersion'] = self::VERSION;
        if (!empty($this->settings['developerMode'])) {
            $data['developerMode'] = true;
        }

        require_once $this->settings['path']['assets'] . "/init.php";
        $content = ob_get_clean();
        unset($data, $tabs);

        $data = ['requestMethod' => $_SERVER['REQUEST_METHOD']];
        foreach ([
            "serverTime",
            "phpVersion",
            "pageTotalTime",
            "memoryUsage",
            "peakMemoryUsage",
            "includedFiles",
        ] as $key) {
            $data[$key] = d::getInstance()->getDataByType($key, $bench);
        }
        $data = ',' .trim(json_encode($data), '{}');
        return str_replace(',"placeholder":0', $data, $content);
    }

    /**
     * Prepare string
     *
     * @param  string $string
     * @param  array  $options  Reserved array for functionality enhancement
     * @return string
     * @see    Debeetle::write()
     */
    public function renderString($string, array $options = [])
    {
        $options += [
            'htmlEntities' => false,
            'nl2br'        => true
        ];
        if(
            isset($options['encoding']) &&
            empty($options['skipEncoding']) &&
            "UTF-8" !== $options['encoding']
        ) {
            $string = mb_convert_encoding($string, "UTF-8", $options['encoding']);
        }
        if ($options['htmlEntities']) {
            $string = htmlentities($string, ENT_COMPAT, "UTF-8");
        }
        if ($options['nl2br']) {
            $string = nl2br($string);
        }
        return $string;
    }

    /**
     * Returns tabs HTML-code
     *
     * @param  array|Node $struct
     * @param  bool $active
     * @return string
     */
    protected function getTab($struct, $active = false)
    {
        if (is_array($struct)) {
            $tabs = [];
            foreach ($struct as $name => $nextStruct) {
                $tabs[] =
                    sprintf(
                        "%s: %s",
                        $this->prepareForJS($name),
                        $this->getTab(
                            $nextStruct,
                            is_object($nextStruct)
                                ? $nextStruct->isActive()
                                : $active
                        )
                    );
            }
            return
                sprintf(
                    "{%stabs: {%s}}",
                    $active ? 'active: true, ': '',
                    implode(',', $tabs)
                );
        } else if ($struct->isDisabled()) {
            return 'false';
        } else {
            return
                sprintf(
                    "{%scontent: %s}",
                    $active ? 'active: true, ': '',
                    // $this->prepareForJS($struct->get(), $active)
                    $this->prepareForJS($struct->get())
                );
        }
    }

    /**
     * Prepares string for JS usage.
     *
     * @param  string $string  String to prepare
     * @return string
     * @todo   Try to extract this method to rhe common library?
     */
    protected function prepareForJS($string)
    {
        return
            "'" .
            str_replace(
                [
                   "'",
                   "\r\n",
                   "\n\r",
                   "\r",
                   "\n",
//                   '\\\\',
                   '</',
                   '/>'
                ],
                [
                   "\\'",
                   '\\n',
                   '\\n',
                   '\\n',
                   '\\n',
//                   '\\\\\\\\',
                   "<' + '/",
                   "/' + '>"
                ],
                $string
            ) .
            "'";
    }
}
