<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SandboxConfig;
use Revolution\Copilot\Types\Rpc\SandboxConfigAuth;
use Revolution\Copilot\Types\Rpc\SandboxConfigUserPolicy;
use Revolution\Copilot\Types\Rpc\SandboxConfigUserPolicyExperimental;
use Revolution\Copilot\Types\Rpc\SandboxConfigUserPolicyExperimentalSeatbelt;
use Revolution\Copilot\Types\Rpc\SandboxCredentialsConfig;
use Revolution\Copilot\Types\Rpc\SandboxMaskedEnvVar;

describe('SandboxConfig', function () {
    it('round-trips auth, credentials, and experimental policy from arrays', function () {
        $data = [
            'enabled' => true,
            'userPolicy' => [
                'experimental' => [
                    'seatbelt' => ['keychainAccess' => false],
                ],
            ],
            'auth' => ['git' => true, 'gh' => false],
            'credentials' => [
                'envVars' => [
                    'APP_CREDENTIAL' => ['injectHosts' => ['registry.example.com']],
                ],
            ],
        ];

        $config = SandboxConfig::fromArray($data);

        expect($config->auth)->toBeInstanceOf(SandboxConfigAuth::class)
            ->and($config->credentials)->toBeInstanceOf(SandboxCredentialsConfig::class)
            ->and($config->credentials->envVars['APP_CREDENTIAL'])->toBeInstanceOf(SandboxMaskedEnvVar::class)
            ->and($config->userPolicy)->toBeInstanceOf(SandboxConfigUserPolicy::class)
            ->and($config->userPolicy->experimental)->toBeInstanceOf(SandboxConfigUserPolicyExperimental::class)
            ->and($config->userPolicy->experimental->seatbelt)->toBeInstanceOf(SandboxConfigUserPolicyExperimentalSeatbelt::class)
            ->and($config->toArray())->toBe($data);
    });

    it('serializes typed auth, credentials, and experimental policy', function () {
        $config = new SandboxConfig(
            enabled: true,
            userPolicy: new SandboxConfigUserPolicy(
                experimental: new SandboxConfigUserPolicyExperimental(
                    seatbelt: new SandboxConfigUserPolicyExperimentalSeatbelt(keychainAccess: false),
                ),
            ),
            auth: new SandboxConfigAuth(git: true, gh: false),
            credentials: new SandboxCredentialsConfig(
                envVars: [
                    'APP_CREDENTIAL' => new SandboxMaskedEnvVar(injectHosts: ['registry.example.com']),
                ],
            ),
        );

        expect($config->toArray())->toBe([
            'enabled' => true,
            'userPolicy' => [
                'experimental' => [
                    'seatbelt' => ['keychainAccess' => false],
                ],
            ],
            'auth' => ['git' => true, 'gh' => false],
            'credentials' => [
                'envVars' => [
                    'APP_CREDENTIAL' => ['injectHosts' => ['registry.example.com']],
                ],
            ],
        ]);
    });
});
