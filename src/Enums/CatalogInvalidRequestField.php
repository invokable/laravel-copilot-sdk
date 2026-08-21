<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Which request field was rejected before any work was done.
 *
 * @experimental
 */
enum CatalogInvalidRequestField: string
{
    /** The search query was empty or longer than permitted. */
    case Query = 'query';

    /** The requested result count fell outside its permitted range. */
    case Limit = 'limit';

    /** The requested candidate kinds were empty or contained a duplicate. */
    case Kinds = 'kinds';

    /** The negotiation block was missing or malformed. */
    case Contract = 'contract';

    /** The plan source was missing or malformed. */
    case Source = 'source';

    /** The supplied card was missing its media type, URL, or data. */
    case Card = 'card';

    /** The requested configuration scope is not one this runtime writes. */
    case Scope = 'scope';
}
