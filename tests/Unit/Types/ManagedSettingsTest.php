<?php

declare(strict_types=1);

use Revolution\Copilot\Types\ManagedSettings;
use Revolution\Copilot\Types\ManagedSettingsPermissions;

it('round trips managed settings permissions', function () {
    $settings = new ManagedSettings(
        permissions: new ManagedSettingsPermissions(
            disableAssistedPermissionsMode: true,
            deny: ['Shell(rm *)'],
            limitTo: ['Domain(example.com)', 'Domain(*.example.org)'],
        ),
    );

    expect(ManagedSettings::fromArray($settings->toArray())->toArray())
        ->toBe(['permissions' => [
            'deny' => ['Shell(rm *)'],
            'disableAssistedPermissionsMode' => true,
            'limitTo' => ['Domain(example.com)', 'Domain(*.example.org)'],
        ]]);
});

it('preserves an explicit false assisted-permissions restriction', function () {
    expect((new ManagedSettingsPermissions(disableAssistedPermissionsMode: false))->toArray())
        ->toBe(['disableAssistedPermissionsMode' => false]);
});
