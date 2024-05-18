<?php
/**
 * PHP Debugging & Benchmarking Tools.
 *
 * @author [deepeloper](https://github.com/deepeloper)
 * @license [MIT](https://opensource.org/licenses/mit-license.php)
 */

namespace deepeloper\Debeetle;

class Logger
{
    /**
     * Settings
     *
     * @var array
     */
    protected $settings =
        [
            'maxSize' => 2000000,
            'mask'    => E_ALL
        ];

    protected $masks = [
        E_ERROR             => 'PHP: FATAL ERROR',
        E_WARNING           => 'PHP: WARNING',
        E_PARSE             => 'PHP: PARSE ERROR',
        E_NOTICE            => 'PHP: NOTICE',
        E_CORE_ERROR        => 'PHP: CORE ERROR',
        E_CORE_WARNING      => 'PHP: CORE WARNING',
        E_COMPILE_ERROR     => 'PHP: COMPILE ERROR',
        E_COMPILE_WARNING   => 'PHP: COMPILE WARNING',
        E_USER_ERROR        => 'USER: ERROR',
        E_USER_WARNING      => 'USER: WARNING',
        E_USER_NOTICE       => 'USER: NOTICE',
        E_RECOVERABLE_ERROR => 'PHP: RECOVERABLE ERROR',
        E_DEPRECATED        => 'PHP: DEPRECATED',
        E_USER_DEPRECATED   => 'USER: DEPRECATED'
    ];

    /**
     * Constructor
     *
     * @param array $settings Settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings + $this->settings;
    }

    /**
     * Set logging mask
     *
     * @param  int $mask  Mask
     * @return void
     */
    public function setMask($mask)
    {
        $this->settings['mask'] = (int)$mask;
    }

    /**
     * Logs message
     *
     * @param  string $message  Message
     * @param  int    $level    Level
     * @return void
     */
    public function log($message, $level = E_USER_NOTICE)
    {
        if (
            !isset($this->settings['path']) ||
            !($level & $this->settings['mask'])
        ) {
            return;
        }
        $message =
            '## ' . date('Y-m-d H:i:s') . ' ## ' .
            (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0') .
            ' ## ' . $this->masks[$level] . ' ## ' .
            $message . "\n";
        clearstatcache();
        $path = $this->settings['path'];
        if(
            $this->settings['maxSize'] > 0 &&
            @file_exists($path) &&
            @filesize($path) >= $this->settings['maxSize']
        ){
            $backup = $path . '.bak';
            @unlink($backup);
            @rename($path, $backup);
        }
        @file_put_contents($path, $message, FILE_APPEND);
        @chmod($path, 0666);
    }
}
