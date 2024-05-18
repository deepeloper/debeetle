<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Registry;

class Registry implements RegistryInterface
{
    /**
     * Registry scope
     *
     * @var array
     */
    protected $scope = ['_default' => []];

    /**
     * Default scope source
     *
     * @var   mixed  Array or string
     */
    protected $defaultSource = '_default';

    /**
     * Returns value by specified key
     *
     * @param  string $key      Registry key
     * @param  mixed  $default  Default value, will be returned if there is
     *                          no key in the request
     * @param  mixed  $source   Scope source, string or array
     *                          (several sources will be scanned),
     *                          if not specified, default source is used
     * @return mixed
     */
    public function get($key, $default = null, $source = '_default')
    {
        $this->patchSource($source);
        if (is_array($source)) {
            foreach ($source as $src) {
                $result = $this->get($key, null, $src);
                if (!is_null($result)) {
                    return $result;
                }
            }
            return $default;
        }
        return
            isset($this->scope[$source][$key])
            ? $this->scope[$source][$key]
            : $default;
    }

    /**
     * Returns whole scope
     *
     * If $source argument is array, the sum of scopes will be returned.
     *
     * @param  mixed $source  Scope source, if not specified,
     *                        default source is used
     * @return array
     */
    public function getScope($source = '_default')
    {
        $this->patchSource($source);
        if (!is_array($source)) {
            return $this->scope[$source];
        }
        $scope = [];
        foreach ($source as $src) {
            $scope += $this->getScope($src);
        }
        return $scope;
    }

    /**
     * Sets/unsets value by key
     *
     * If $source argument is array, first source scope will be modified.
     *
     * @param  string $key     Registry key
     * @param  mixed  $value   Null to unset
     * @param  string $source  Scope source, if not specified,
     *                         default source is used
     * @return Registry
     */
    public function set($key, $value = null, $source = '_default')
    {
        $this->patchSource($source, true);
        if (is_null($value)) {
            unset($this->scope[$source][$key]);
        } else {
            $this->scope[$source][$key] = $value;
        }
        return $this;
    }

    /**
     * Sets whole scope
     *
     * @param  array  $scope  Scope
     * @param  string $source  Scope source
     * @return Registry
     */
    public function setScope(array $scope, $source)
    {
        $this->scope[$source] = $scope;
        return $this;
    }

    /**
     * Resets scope
     *
     * @param  string $source  Scope source, if not specified,
     *                         all scopes will be reset
     * @return Registry
     */
    public function reset($source = null)
    {
        if (is_null($source)) {
            foreach (array_keys($this->scope) as $source) {
                $this->scope[$source] = [];
            }
        } else {
            $this->scope[$source] = [];
        }
        return $this;
    }

    /**
     * Sets default scope
     *
     * @param  mixed $source  Scope source, array or string
     * @return Registry
     */
    public function setDefaultSource($source)
    {
        $this->defaultSource = $source;
        return $this;
    }

    /**
     * Replace source by default value if needed
     *
     * @param  string &$source       Scope source
     * @param  bool   $extractFirst  Extract first source from array
     * @return void
     */
    protected function patchSource(&$source, $extractFirst = false) {
        if ($source == '_default') {
            $source = $this->defaultSource;
        }
        if ($extractFirst && is_array($source)) {
            $source = $source[0];
        }
    }
}
