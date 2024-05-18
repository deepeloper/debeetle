<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle;

class PublicAsset
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $request;

    /**
     * Source to HTTP content type
     *
     * @var array
     */
    protected $sourceToContentType = [
        'frame' => "text/html",
        'template' => "text/html",
        'asset' => [
            'css' => "text/css",
            'less' => "text/css",
            'lessJs' => "text/javascript",
            'js' => "text/javascript",
        ],
    ];

    /**
     * Constructor.
     *
     * @param string $configPath  Debeetle config file system path
     * @param array  $request     Request array
     */
    public function __construct($configPath, array $request)
    {
        $this->request = $request;
        $this->settings = Loader::startup(['config' => $configPath],false);
        if (empty($this->settings)) {
            return;
        }
        if (empty($this->request['plugin'])) {
            $this->validateRequest();
            $this->processRequest();
        } else {
            $this->processPluginRequest();
        }
    }

    /**
     * Validate request data
     *
     * @return void
     * @exitpoint
     */
    protected function validateRequest()
    {
        if (!isset($this->request['source'])) {
            $this->send404Header("Missing obligatory request argument 'source'");
        }
        $source = $this->request['source'];
        $invalidSource = true;
        if (isset($this->sourceToContentType[$source])) {
            $contentType = $this->sourceToContentType[$source];
            if (is_array($contentType)) {
                if (isset($contentType[$this->request['type']])) {
                    $invalidSource = false;
                }
            } else {
                $invalidSource = false;
            }
        }
        if ($invalidSource) {
            $this->send404Header("Invalid 'source' request argument");
        }
        $obligatoryArgs = ["v"];
        switch ($source) {
            case "asset":
                if (isset($this->request['target']) && "parent" !== $this->request['target']) {
                    $obligatoryArgs += ["type", "skin", "theme", "h"];
                }
                break;

            case "template":
                $obligatoryArgs += ["skin"];
                break;
        }
        foreach ($obligatoryArgs as $arg) {
            if (empty($this->request[$arg])) {
                $this->send404Header("Missing obligatory request argument '$arg'");
            }
        }
    }

    /**
     * Process request data and exit.
     *
     * Sends appropriate asset or 404.
     *
     * @return void
     */
    protected function processRequest()
    {
        $source = $this->request['source'];
        $contentType = $this->sourceToContentType[$source];
        if (is_array($contentType)) {
            $type = $this->request['type'];
            $contentType = $contentType[$type];
        }
        header("Content-Type: $contentType");
        if (empty($this->request['dev'])) {
            header("Cache-Control: max-age=31536000");
        } else {
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
        $settings = $this->settings;
        $path = $settings['path']['assets'];
        switch ($source) {
            case "template":
                $_GET['type'] = "html";
                $settings['plugin'] = [];
                require "$path/asset.php";
                break;

            case "asset":
            case "frame":
                require "$path/$source.php";
                break;
        }
        die;
    }

    /**
     * Validate and process plugin request.
     *
     * @return void
     * @exitpoint
     */
    protected function processPluginRequest()
    {
        if (empty($this->settings)) {
            $this->send404Header();
        }
        $plugin = (string)$this->request['plugin'];
        if (isset($this->settings['plugin'][$plugin]) && class_exists($this->settings['plugin'][$plugin]['class'])) {
            $plugin = new $this->settings['plugin'][$plugin]['class'];
            $plugin->processRequest();
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
}
