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

    /** The supplied interaction outcome was not recognized. */
    case Outcome = 'outcome';

    /** The requested catalog page was invalid. */
    case Page = 'page';

    /** The policy session was missing or unusable. */
    case PolicySessionId = 'policySessionId';

    /** The catalog selection reference was missing or unusable. */
    case SelectionRef = 'selectionRef';

    /** The session identifier was missing or unusable. */
    case SessionId = 'sessionId';

    /** The requested categories were invalid. */
    case Categories = 'categories';

    /** The requested feed was missing or invalid. */
    case Feed = 'feed';

    /** The requested trust tiers were invalid. */
    case TrustTiers = 'trustTiers';
}
