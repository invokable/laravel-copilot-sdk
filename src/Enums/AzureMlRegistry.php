<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Registry that owns an Azure ML model version.
 *
 * @experimental
 */
enum AzureMlRegistry: string
{
    case AzureMl = 'azureml';
    case AzureMlDev = 'azureml-dev';
}
