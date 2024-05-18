<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle;

class Asset
{
    /**
     * Settings
     *
     * @var array
     */
    protected $settings;

    /**
     * @param string $path  Assets path
     * @param array $request  Request
     */
    public function __construct($path, array $request, array $settings)
    {
        $this->settings = $settings;
        $this->sendResponse($path, $request);
    }

    /**
     * Send appropriate response
     *
     * @param string $path  Assets path
     * @param array $request  Request
     * @exitpoint  In case of troubles with assets
     */
    private function sendResponse($path, array $request)
    {
        $files = $this->getFiles($path, $request);
        $this->validateFiles($files);
        $settings = $this->settings; // Used in addon files.
        foreach ($files as $struct) {
            if (is_file($struct['path']) && is_readable($struct['path'])) {
                require $struct['path'];
            }
        }
        if ($request['type'] === 'js') {
            $locales = $settings['locales'];
            array_walk($locales, [$this, 'convertStringCallback']);
            echo '$d.setDictionary({';
            $lastIndex = sizeof($locales);
            $index = 0;
            foreach ($locales as $key => $caption) {
                echo "'$key': $caption", ++$index < $lastIndex ? ', ' : '';
            }
            echo "});\n";
        }
    }

    /**
     * Returns assets depending on type.
     *
     * @param string $path  Assets path
     * @param array $request  Request
     * @return array
     */
    private function getFiles($path, array $request)
    {
        $files = [];
        $skinPath = call_user_func(
            [
                $this->settings['skin'][$request['skin']]['class'],
                "getPath"
            ]
        );
        $themePath = call_user_func(
            [
                $this->settings['skin'][$request['skin']]['theme'][$request['theme']]['class'],
                "getPath"
            ]
        );

        switch ($request['type']) {
            case "lessJs":
                if (empty($request['noskin'])) {
                    $files[] = [
                        'path' => "$skinPath/assets/skin.less.js",
                        'required' => false,
                    ];
                }
                $files[] = [
                    'path' => "$themePath/assets/theme.less.js",
                    'required' => false,
                ];
                break; // case "lessJs"

            case "js":
                $min = empty($request['dev']) ? "" : ".min";
                $files[] = [
                    'path' => "$path/jquery-1.8.3$min.js",
                    'required' => true,
                ];
                $files[] = [
                    'path' => "$path/jquery.cookie.js",
                    'required' => true,
                ];
                $files[] = [
                    'path' => "$path/less.js",
                    'required' => true,
                ];
                $files[] = [
                    'path' => "$path/common.js",
                    'required' => true,
                ];
                foreach ($this->settings['plugin'] as $id => $plugin) {
                    $asset = $this->getPluginAsset($id,"js");
                    if (null === $asset) {
                        continue;
                    }
                    $pluginPath = call_user_func([$plugin['class'], "getPath"]);
                    $files[] = [
                        'path' => "$pluginPath/assets/$asset",
                        'required' => true,
                        'plugin' => [
                            'id' => $id,
                            'name' => $plugin['class'],
                        ],
                    ];
                }
                $files[] = [
                    'path' => "$skinPath/assets/skin.js",
                    'required' => false,
                ];
                $files[] = [
                    'path' => "$themePath/assets/theme.js",
                    'required' => false,
                ];
                break; // case "js"

            case "less":
            case "css":
                if (empty($request['target'])) {
                    $files[] = [
                        'path' => "$skinPath/assets/skin.less",
                        'required' => true,
                    ];
                    $files[] = [
                        'path' => "$themePath/assets/theme.less",
                        'required' => false,
                    ];
                    foreach ($this->settings['plugin'] as $id => $plugin) {
                        $asset = $this->getPluginAsset($id, "less");
                        if (null === $asset) {
                            continue;
                        }
                        $pluginPath = call_user_func([$plugin['class'], "getPath"]);
                        $files[] = [
                            'path' => "$pluginPath/assets/$asset",
                            'required' => true,
                        ];
                    }
                } else {
                    $files[] = [
                        'path' => "$path/frame.css",
                        'required' => true,
                    ];
                }
                break; // case "less"

            case "html":
                $files[] = [
                    'path' => "$skinPath/assets/skin.html",
                    'required' => false,
                ];
                break; // case "html"
        }
        return $files;
    }

    /**
     * @param int $id
     * @param string $type
     * @return ?string
     */
    protected function getPluginAsset($id, $type)
    {
        if (
            !is_array($this->settings['plugin'][$id]) ||
            empty($this->settings['plugin'][$id]['assets'])
        ) {
            return null;
        }
        return isset($this->settings['plugin'][$id]['assets'][$type])
            ? $this->settings['plugin'][$id]['assets'][$type]
            : null;
    }

    /**
     * Validate asset files
     *
     * @param  array $files
     * @return void
     * @exitpoint            In case of troubles with path
     */
    private function validateFiles($files)
    {
        foreach ($files as $struct) {
            if (
                $struct['required'] && (
                    !is_file($struct['path']) ||
                    !is_readable($struct['path'])
                )
            ) {
                $this->send404Header(
                    sprintf(
                        "Required file '%s' not found or cannot be read",
                        $struct['path']
                    )
                );
            }
        }
    }

    /**
     * @param ?string $reason  Reason (for debug purpose only)
     * @return void
     * @exitpoint
     */
    private function send404Header($reason = "")
    {
        $protocol = @getenv('SERVER_PROTOCOL');
        if (!$protocol) {
            $protocol = 'HTTP/1.1';
        }
        header("$protocol 404 Not Found");
        if (empty($this->settings['developerMode'])) {
            die;
        } else {
            die($reason);
        }
    }

    private function convertStringCallback(&$string)
    {
        $string = $this->convertStringToJS($string);
    }

    /**
     * @param string $string
     * @return string
     * @todo Try ro extract this method to the common library?
     */
    private function convertStringToJS($string)
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
//                    '\\\\',
                    '</',
                    '/>'
                ],
                [
                    "''",
                    '\\n',
                    '\\n',
                    '\\n',
                    '\\n',
//                    '\\\\\\\\',
                    "<' + '/",
                    "/' + '>"
                ],
                $string
            ) .
            "'";
    }
}
