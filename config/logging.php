<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    | Controlled by: LOG_CHANNEL in .env file
    | Default: 'stack'
    |
    | Available channels: stack, single, daily, slack, papertrail, stderr,
    |                     syslog, errorlog, null, emergency
    |
     */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    | Default: null (uses default channel)
    |
     */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | uses the Monolog PHP logging library, which provides powerful log
    | handlers and formatters.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    | Log Levels (severity order):
    | - emergency: System is unusable
    | - alert: Action must be taken immediately
    | - critical: Critical conditions
    | - error: Error conditions
    | - warning: Warning conditions
    | - notice: Normal but significant condition
    | - info: Informational messages
    | - debug: Debug-level messages
    |
    | Controlled by: LOG_LEVEL in .env file
    |
     */

    'channels' => [

        /*
         * Stack Channel
         * Aggregates multiple channels into a single channel.
         * Useful for sending logs to multiple destinations simultaneously.
         */
        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        /*
         * Single Channel
         * Writes all logs to a single file.
         * Good for development and small applications.
         * File: storage/logs/laravel.log
         */
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        /*
         * Daily Channel
         * Creates a new log file each day with automatic rotation.
         * Recommended for production environments.
         * Files: storage/logs/laravel-YYYY-MM-DD.log
         */
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14), // Keep logs for 14 days
            'replace_placeholders' => true,
        ],

        /*
         * Slack Channel
         * Sends log messages to a Slack channel via webhook.
         * Useful for real-time production error notifications.
         * Requires: LOG_SLACK_WEBHOOK_URL in .env
         */
        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', 'Laravel Log'),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        /*
         * Papertrail Channel
         * Sends logs to Papertrail cloud logging service.
         * Requires: PAPERTRAIL_URL and PAPERTRAIL_PORT in .env
         */
        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        /*
         * Standard Error Channel
         * Writes logs to PHP's standard error stream (stderr).
         * Useful for containerized applications and CLI commands.
         */
        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        /*
         * Syslog Channel
         * Sends logs to the system's syslog facility.
         * Useful for centralized logging on Unix/Linux systems.
         */
        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        /*
         * Error Log Channel
         * Uses PHP's native error_log() function.
         * Location depends on php.ini configuration.
         */
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        /*
         * Null Channel
         * Discards all log messages (does nothing).
         * Useful for testing or disabling specific log channels.
         */
        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        /*
         * Emergency Channel
         * Used as a fallback when the primary log channel fails.
         * Always writes to storage/logs/laravel.log
         */
        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];



















