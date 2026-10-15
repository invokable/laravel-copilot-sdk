<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Bounded refusal categories for verified Agent Plugin installation.
 *
 * @experimental
 */
enum PluginInstallationFailureReason: string
{
    case AlreadyInstalled = 'already-installed';
    case Busy = 'busy';
    case ConfirmationInvalid = 'confirmation-invalid';
    case ConfirmationUnavailable = 'confirmation-unavailable';
    case DescriptorInvalid = 'descriptor-invalid';
    case DescriptorNotFound = 'descriptor-not-found';
    case DescriptorUnavailable = 'descriptor-unavailable';
    case DescriptorWithdrawn = 'descriptor-withdrawn';
    case DigestMismatch = 'digest-mismatch';
    case EntrypointUnavailable = 'entrypoint-unavailable';
    case Expired = 'expired';
    case FeatureDisabled = 'feature-disabled';
    case ForeignRuntime = 'foreign-runtime';
    case InvalidCandidate = 'invalid-candidate';
    case InvalidPlugin = 'invalid-plugin';
    case InvalidRequest = 'invalid-request';
    case PayloadMismatch = 'payload-mismatch';
    case PayloadUnavailable = 'payload-unavailable';
    case PlanExpired = 'plan-expired';
    case PlanReplayed = 'plan-replayed';
    case PolicyBlocked = 'policy-blocked';
    case PolicyChanged = 'policy-changed';
    case PolicyContextUnavailable = 'policy-context-unavailable';
    case ReviewTooLarge = 'review-too-large';
    case SearchMismatch = 'search-mismatch';
    case UnsupportedResource = 'unsupported-resource';
    case WriteFailed = 'write-failed';
    case WrongKind = 'wrong-kind';
}
