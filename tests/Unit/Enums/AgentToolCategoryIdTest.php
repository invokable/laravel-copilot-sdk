<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentToolCategoryId;

it('provides the upstream agent tool category identifiers', function () {
    expect(array_map(static fn (AgentToolCategoryId $category): string => $category->value, AgentToolCategoryId::cases()))
        ->toBe(['shell', 'read', 'edit', 'subagents', 'skills', 'web', 'ask-user']);
});
