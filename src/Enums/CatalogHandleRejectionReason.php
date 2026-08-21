<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Why a presented handle was rejected.
 *
 * @experimental
 */
enum CatalogHandleRejectionReason: string
{
    /** The handle is unparseable, unknown, or was issued for a different operation. */
    case Invalid = 'invalid';

    /** The handle's time to live has elapsed. */
    case Stale = 'stale';

    /** The handle has already been used. */
    case Replayed = 'replayed';

    /** The handle was issued by a different runtime instance. */
    case Foreign = 'foreign';
}
