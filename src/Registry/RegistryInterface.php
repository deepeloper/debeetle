<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle\Registry;

interface RegistryInterface
{
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
    public function get($key, $default = null, $source = '_default');

    /**
     * Returns whole scope
     *
     * If $source argument is array, the sum of scopes will be returned.
     *
     * @param  mixed $source  Scope source, if not specified,
     *                        default source is used
     * @return array
     */
    public function getScope($source = '_default');

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
    public function set($key, $value = null, $source = '_default');

    /**
     * Sets whole scope
     *
     * @param  array  $scope  Scope
     * @param  string $source  Scope source
     * @return Registry
     */
    public function setScope(array $scope, $source);

    /**
     * Resets scope
     *
     * @param  string $source  Scope source, if not specified,
     *                         all scopes will be reset
     * @return Registry
     */
    public function reset($source = null);

    /**
     * Sets default scope
     *
     * @param  mixed $source  Scope source, array or string
     * @return Registry
     */
    public function setDefaultSource($source);
}
