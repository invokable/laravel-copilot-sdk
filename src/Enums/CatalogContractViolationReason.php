<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Which wire-contract rule an upstream response broke.
 *
 * @experimental
 */
enum CatalogContractViolationReason: string
{
    /** A result carried both a URL and embedded data. */
    case BothUrlAndData = 'both-url-and-data';

    /** A result carried neither a URL nor embedded data. */
    case NeitherUrlNorData = 'neither-url-nor-data';

    /** Two results claimed the same normalised identity. */
    case DuplicateIdentity = 'duplicate-identity';

    /** A result declared no media type, or one this contract does not model. */
    case UnknownMediaType = 'unknown-media-type';
}
