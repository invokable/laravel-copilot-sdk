<?php

declare(strict_types=1);

namespace Revolution\Copilot\Enums;

/**
 * Discriminator for a candidate-backed install-plan source.
 *
 * @experimental
 */
enum McpPlanInstallSourceCandidateKind: string
{
    /** Plan from a candidate returned by catalog search. */
    case Candidate = 'candidate';
}
