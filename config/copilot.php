<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Copilot CLI Path
    |--------------------------------------------------------------------------
    |
    | The path to the Copilot CLI executable. Defaults to 'copilot' which
    | assumes the CLI is available in your system PATH.
    |
    */
    'cli_path' => env('COPILOT_CLI_PATH', 'copilot'),

    /*
    |--------------------------------------------------------------------------
    | CLI Arguments
    |--------------------------------------------------------------------------
    |
    | Additional arguments to pass to the CLI when starting the server.
    |
    */
    'cli_args' => [],

    /*
    |--------------------------------------------------------------------------
    | Working Directory
    |--------------------------------------------------------------------------
    |
    | The working directory for the CLI process. If null, uses the
    | application's base path.
    |
    */
    'cwd' => null,

    /*
    |--------------------------------------------------------------------------
    | Log Level
    |--------------------------------------------------------------------------
    |
    | The log level for the CLI server. Options: none, error, warning, info, debug, all
    |
    */
    'log_level' => env('COPILOT_LOG_LEVEL', 'info'),

    /*
    |--------------------------------------------------------------------------
    | Default Timeout
    |--------------------------------------------------------------------------
    |
    | The default timeout in seconds for sendAndWait operations.
    |
    */
    'timeout' => env('COPILOT_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default model to use for sessions when not specified.
    |
    */
    'model' => env('COPILOT_MODEL'),
];
