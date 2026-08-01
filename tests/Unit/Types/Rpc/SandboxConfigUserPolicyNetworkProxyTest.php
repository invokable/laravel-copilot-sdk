<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SandboxConfigUserPolicyNetworkProxy;

describe('SandboxConfigUserPolicyNetworkProxy', function () {
    it('creates with only a url', function () {
        $proxy = new SandboxConfigUserPolicyNetworkProxy(url: 'http://proxy.example.com:8080');

        expect($proxy->url)->toBe('http://proxy.example.com:8080')
            ->and($proxy->username)->toBeNull()
            ->and($proxy->password)->toBeNull()
            ->and($proxy->toArray())->toBe(['url' => 'http://proxy.example.com:8080']);
    });

    it('creates with credentials and serializes all fields', function () {
        $proxy = new SandboxConfigUserPolicyNetworkProxy(
            url: 'https://proxy.example.com',
            password: '${secret:proxy}',
            username: 'ci',
        );

        expect($proxy->toArray())->toBe([
            'url' => 'https://proxy.example.com',
            'password' => '${secret:proxy}',
            'username' => 'ci',
        ]);
    });

    it('round-trips through fromArray', function () {
        $data = [
            'url' => 'http://proxy.example.com:8080',
            'password' => 'secret',
            'username' => 'ci',
        ];

        expect(SandboxConfigUserPolicyNetworkProxy::fromArray($data)->toArray())->toBe($data);
    });
});
