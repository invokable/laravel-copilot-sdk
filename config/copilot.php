<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Copilot Server URL (TCP Mode)
    |--------------------------------------------------------------------------
    |
    | The URL of an existing Copilot CLI server to connect to.
    | When set, the SDK will connect to this server instead of starting
    | a new CLI process.
    |
    | Example: tcp://127.0.0.1:12345
    |
    | To start the server manually:
    |   copilot --server --port 12345
    |
    */
    'url' => env('COPILOT_URL'),

    /*
    |--------------------------------------------------------------------------
    | Copilot CLI Path
    |--------------------------------------------------------------------------
    |
    | The path to the Copilot CLI executable. Defaults to 'copilot' which
    | assumes the CLI is available in your system PATH.
    | If you specify `null`, the executable file will be searched automatically.
    |
    | Note: This option is ignored when 'url' is set (TCP mode).
    |
    */
    'cli_path' => env('COPILOT_CLI_PATH', 'copilot'),

    /*
    |--------------------------------------------------------------------------
    | CLI Arguments
    |--------------------------------------------------------------------------
    |
    | Additional arguments to pass to the CLI when starting the server.
    | e.g. ['--yolo']
    |
    | Note: This option is ignored when 'url' is set (TCP mode).
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
    | Note: This option is ignored when 'url' is set (TCP mode).
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
    | Note: This option is ignored when 'url' is set (TCP mode).
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
