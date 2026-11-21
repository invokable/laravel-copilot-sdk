<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentsCreateFileResultKind;

it('provides success and error result kinds', function () {
    expect(array_map(
        static fn (AgentsCreateFileResultKind $kind): string => $kind->value,
        AgentsCreateFileResultKind::cases(),
    ))->toBe(['success', 'error']);
});
