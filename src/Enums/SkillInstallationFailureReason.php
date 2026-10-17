<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Bounded refusal reasons for Skill installation management operations.
 *
 * @experimental
 */
enum SkillInstallationFailureReason: string
{
    case FeatureDisabled = 'feature-disabled';
    case InvalidRequest = 'invalid-request';
    case OperationLimit = 'operation-limit';
    case Cancelled = 'cancelled';
    case ConfirmationUnavailable = 'confirmation-unavailable';
    case ConfirmationInvalid = 'confirmation-invalid';
    case PolicyContextUnavailable = 'policy-context-unavailable';
    case PolicyChanged = 'policy-changed';
    case ResourceNotFound = 'resource-not-found';
    case PlanExpired = 'plan-expired';
    case PlanReplayed = 'plan-replayed';
    case ForeignRuntime = 'foreign-runtime';
    case WrongKind = 'wrong-kind';
    case SearchMismatch = 'search-mismatch';
    case Expired = 'expired';
    case InvalidCandidate = 'invalid-candidate';
    case DescriptorUnavailable = 'descriptor-unavailable';
    case DescriptorInvalid = 'descriptor-invalid';
    case EntrypointUnavailable = 'entrypoint-unavailable';
    case InvalidSkill = 'invalid-skill';
    case PayloadUnavailable = 'payload-unavailable';
    case PayloadMismatch = 'payload-mismatch';
    case SourceRateLimited = 'source-rate-limited';
    case NoCredential = 'no-credential';
    case CredentialRejected = 'credential-rejected';
    case SourceChanged = 'source-changed';
    case LifecycleUnavailable = 'lifecycle-unavailable';
    case RecoveryRequired = 'recovery-required';
    case ReviewTooLarge = 'review-too-large';
    case Busy = 'busy';
    case AlreadyInstalled = 'already-installed';
    case ConfigurationModified = 'configuration-modified';
    case WriteFailed = 'write-failed';
    case InvalidReference = 'invalid-reference';
    case DescriptorNotFound = 'descriptor-not-found';
    case DescriptorWithdrawn = 'descriptor-withdrawn';
    case DigestMismatch = 'digest-mismatch';
    case UnsupportedResource = 'unsupported-resource';
}
