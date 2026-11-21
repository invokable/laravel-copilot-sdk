<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentFileLocation;

it('provides project and user agent file locations', function () {
    expect(array_map(static fn (AgentFileLocation $location): string => $location->value, AgentFileLocation::cases()))
        ->toBe(['project', 'user']);
});
