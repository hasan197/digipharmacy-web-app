<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LogService
{
    /**
     * Log authentication related events
     *
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $level Log level (default: info)
     */
    public static function auth(string $message, array $context = [], string $level = 'info'): void
    {
        Log::channel('auth')->$level($message, $context);
    }

    /**
     * Log POS related events
     *
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $level Log level (default: info)
     */
    public static function pos(string $message, array $context = [], string $level = 'info'): void
    {
        Log::channel('pos')->$level($message, $context);
    }

    /**
     * Log error events
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param string $level Log level (default: error)
     */
    public static function error(string $message, array $context = [], string $level = 'error'): void
    {
        Log::channel('errors')->$level($message, $context);
    }

    /**
     * Log database related events
     *
     * @param string $message Log message
     * @param array $context Additional context data
     * @param string $level Log level (default: info)
     */
    public static function db(string $message, array $context = [], string $level = 'info'): void
    {
        Log::channel('database')->$level($message, $context);
    }
}
