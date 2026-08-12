<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ManagedSettings;
use Revolution\Copilot\Types\ManagedSettingsPermissions;

it('round trips managed settings permissions', function () {
    $settings = new ManagedSettings(
        permissions: new ManagedSettingsPermissions(deny: ['Shell(rm *)']),
    );

    expect(ManagedSettings::fromArray($settings->toArray())->toArray())
        ->toBe(['permissions' => ['deny' => ['Shell(rm *)']]]);
});
