<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle;

use deepeloper\Debeetle\Registry\Registry;

class HTTPRequest extends Registry
{
    /**
     * @var HTTPRequest
     */
    protected static $instance;

    /**
     * Registry scope
     *
     * @var array
     */
    protected $scope = [];

    /**
     * Default scope source
     *
     * @var   mixed  Array or string
     */
    protected $defaultSource = ["g", "p"];

    /**
     * Returns Deepelopment_HTTPRequest instance
     *
     * @return HTTPRequest
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new HTTPRequest;
        }
        return self::$instance;
    }

    public function __construct()
    {
        foreach (
            [
                '_GET'    => 'g',
                '_POST'   => 'p',
                '_COOKIE' => 'c',
                '_FILES'  => 'f',
            ] as $source => $scopeSource
        ) {
            $this->setScope($GLOBALS[$source], $scopeSource);
        }
        if (function_exists("get_magic_quotes_gpc") && @get_magic_quotes_gpc()) {
            $this->scope = $this->recursiveStripSlashes($this->scope);
        }
    }

    protected function recursiveStripSlashes($entity)
    {
        if(is_array($entity)){
            $entity = array_map([$this, 'recursiveStripSlashes'], $entity);
        }elseif(is_string($entity)){
            $entity = stripslashes($entity);
        }
        return $entity;
    }
}
