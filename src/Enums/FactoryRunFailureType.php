<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for a factory run failure.
 *
 * @experimental This enum is part of an experimental API and may change or be removed.
 */
enum FactoryRunFailureType: string
{
    case FACTORY_LIMIT_REACHED = 'factory_limit_reached';
    case FACTORY_RESUME_DECLINED = 'factory_resume_declined';
}
