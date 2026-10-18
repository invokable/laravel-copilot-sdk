<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AuthReadValue;

it('preserves credential-free authentication identity data in auth reads', function () {
    $read = AuthReadValue::fromArray([
        'kind' => 'identity',
        'authInfo' => ['host' => 'github.com', 'login' => 'octocat', 'copilotUser' => ['id' => 'user-1']],
    ]);

    expect($read->toArray()['authInfo'])->toBe([
        'host' => 'github.com',
        'login' => 'octocat',
        'copilotUser' => ['id' => 'user-1'],
    ]);
});
