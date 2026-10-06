<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Revolution\Copilot\Types\SkillProviderCallOptions;

/**
 * Supplies a session-scoped skill catalog and lazily loaded Markdown content.
 *
 * Implementations should be safe for concurrent callbacks.
 */
interface SkillProvider
{
    /**
     * @return array<array<string, mixed>>
     */
    public function listSkills(SkillProviderCallOptions $options): array;

    public function readSkill(string $name, SkillProviderCallOptions $options): ?string;
}
