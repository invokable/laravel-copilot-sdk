<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Where a required value is applied when the planned server is launched.
 *
 * @experimental
 */
enum McpPlanValueCategory: string
{
    /** Set as an environment variable on the launched process. */
    case EnvironmentVariable = 'environment-variable';

    /** Passed to the runtime that launches the package. */
    case RuntimeArgument = 'runtime-argument';

    /** Passed to the packaged server itself. */
    case PackageArgument = 'package-argument';

    /** Sent as a request header to a remote endpoint. */
    case Header = 'header';

    /** Substituted into the remote endpoint URL. */
    case UrlVariable = 'url-variable';
}
