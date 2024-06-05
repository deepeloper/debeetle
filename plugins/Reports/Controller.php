<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Plugin\Reports;

use deepeloper\Debeetle\Plugin\AbstractController;
use Exception;

/**
 * Reports plugin.
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
    const VERSION = "1.0.1";

    /**
     * @var string
     */
    protected $template;

    /**
     * @var ?callable-string
     */
    protected $previousHandler = null;

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
        $options = &$this->settings['defaults']['options']['errorHandler'];
        $this->setOptionsType($options, ["tab", "template"], "string");
        $this->setOptionsType($options, ["place"], "array");
        $this->setOptionsType($options, ["errorReporting", "errorLevels"], "int");
        $this->patchBooleanOptions($options, ["separateTabs", "callPrevious"]);

        $this->template = preg_replace("/ {2,}/", " ", html_entity_decode($options['template']));

//        $this->debeetle->registerMethod("errorHandler", ["deepeloper\\Debeetle\\d", "errorHandler"]);

        error_reporting($options['errorReporting']);
//        $previousHandler = set_error_handler(["deepeloper\\Debeetle\\d", "errorHandler"], $options['errorLevels']);
        $previousHandler = set_error_handler([$this, "errorHandler"], $options['errorLevels']);
        if ($options['callPrevious'] && null !== $previousHandler) {
            $this->previousHandler = $previousHandler;
        }
    }

    /**
     * Displays settings.
     *
     * @return void
     */
    public function displaySettings()
    {
    }

    public function errorHandler($code, $message, $file, $line)
    {
        static
            $count = 0,
            $levels = [
                "ERROR", "WARNING", "PARSE", "NOTICE", "CORE_ERROR", "CORE_WARNING", "COMPILE_ERROR", "COMPILE_WARNING",
                "USER_ERROR", "USER_WARNING", "USER_NOTICE", "STRICT", "RECOVERABLE_ERROR", "DEPRECATED",
                "USER_DEPRECATED", "UNKNOWN",
            ];

        $level = 0;
        foreach ($levels as $level) {
            if (constant("E_$level") === $code) {
                break;
            }
        }
        $message = sprintf(
            $this->template,
            date("Y-m-d H:i:s"),
            ++$count,
            strtolower($level),
            $level,
            htmlentities(
                trim($message),
                ENT_QUOTES|ENT_SUBSTITUTE,
                $this->settings['defaults']['options']['write']['encoding']
            )/*,
            str_replace("\\", "/", $file),
            $line,
            nl2br(str_replace(
                "\\", "/", preg_replace("/^#0?.+\n/", "", $e->getTraceAsString())
            ))*/
        );
        $e = new Exception();

        if (null === $this->debeetle->getTrace()) {
            $this->debeetle->setTrace(1);
        }
        $tabId = $this->settings['defaults']['options']['errorHandler']['tabId'];
        $palces = $this->settings['defaults']['options']['errorHandler']['place'];
        if ($this->settings['defaults']['options']['errorHandler']['separateTabs']) {
            $tabId .= "|$level";
        }
        $this->debeetle->tab($tabId, null, $palces/*, ['skipInternalBench' => true]*/);
        $this->debeetle->write($message, [/*'skipInternalBench' => true, */'htmlEntities' => false, 'nl2br' => false]);
//        $this->debeetle->write(str_replace(
//            "\\", "/", preg_replace("/^#0?.+\n/", "", $e->getTraceAsString())
//        ));###
        $this->debeetle->trace();

        if (null !== $this->previousHandler) {
            call_user_func_array($this->previousHandler, [$code, $message, $file, $line]);
        }
        return true;
    }
}
