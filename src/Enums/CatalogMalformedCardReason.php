<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * How a card failed validation.
 *
 * @experimental
 */
enum CatalogMalformedCardReason: string
{
    /** The document is not well-formed JSON. */
    case InvalidJson = 'invalid-json';

    /** The document does not satisfy its media type's schema. */
    case SchemaViolation = 'schema-violation';

    /** The declared media type is not one this runtime understands. */
    case UnsupportedMediaType = 'unsupported-media-type';

    /** A field the media type requires is absent. */
    case MissingRequiredField = 'missing-required-field';

    /** The document exceeded the permitted size. */
    case SizeLimitExceeded = 'size-limit-exceeded';
}
