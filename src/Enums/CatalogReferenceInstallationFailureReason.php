<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Bounded refusal categories for planning from a materialisation reference.
 *
 * @experimental
 */
enum CatalogReferenceInstallationFailureReason: string
{
    case AlreadyInstalled = 'already-installed';
    case Busy = 'busy';
    case ConfirmationUnavailable = 'confirmation-unavailable';
    case DescriptorInvalid = 'descriptor-invalid';
    case DescriptorNotFound = 'descriptor-not-found';
    case DescriptorUnavailable = 'descriptor-unavailable';
    case DescriptorWithdrawn = 'descriptor-withdrawn';
    case DigestMismatch = 'digest-mismatch';
    case EntrypointUnavailable = 'entrypoint-unavailable';
    case FeatureDisabled = 'feature-disabled';
    case InvalidPlugin = 'invalid-plugin';
    case InvalidReference = 'invalid-reference';
    case InvalidRequest = 'invalid-request';
    case InvalidSkill = 'invalid-skill';
    case OperationLimit = 'operation-limit';
    case PolicyBlocked = 'policy-blocked';
    case PolicyContextUnavailable = 'policy-context-unavailable';
    case ReviewTooLarge = 'review-too-large';
    case UnsupportedResource = 'unsupported-resource';
    case WriteFailed = 'write-failed';
}
