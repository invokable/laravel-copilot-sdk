<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Which kind of opaque handle was presented.
 *
 * @experimental
 */
enum CatalogHandleType: string
{
    /** A search candidate handle. */
    case Candidate = 'candidate';

    /** An install plan handle. */
    case Plan = 'plan';
}
