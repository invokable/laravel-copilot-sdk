<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AdaptiveThinkingSupport;
use Revolution\Copilot\Enums\SandboxSessionChange;
use Revolution\Copilot\Types\ModelCapabilities;
use Revolution\Copilot\Types\Rpc\InstalledPluginInfo;
use Revolution\Copilot\Types\Rpc\ManagedMcpServerConfig;
use Revolution\Copilot\Types\Rpc\ModeSetResult;
use Revolution\Copilot\Types\Rpc\SandboxConfig;
use Revolution\Copilot\Types\Rpc\SandboxConfigUserPolicy;
use Revolution\Copilot\Types\Rpc\SandboxConfigUserPolicyFilesystem;
use Revolution\Copilot\Types\Rpc\SandboxConfigUserPolicyNetwork;
use Revolution\Copilot\Types\Rpc\ShutdownRequest;
use Revolution\Copilot\Types\Rpc\SlashCommandTextResult;

describe('deferred upstream SDK types', function () {
    it('retains installed plugin source metadata', function () {
        $plugin = InstalledPluginInfo::fromArray([
            'name' => 'trusted-plugin',
            'marketplace' => '',
            'enabled' => true,
            'source' => 'builtin',
            'directSourceId' => 'stable-id',
        ]);

        expect($plugin->source)->toBe('builtin')
            ->and($plugin->directSourceId)->toBe('stable-id')
            ->and($plugin->toArray()['source'])->toBe('builtin');
    });

    it('round trips managed MCP server and sandbox policy configuration', function () {
        $server = ManagedMcpServerConfig::fromArray([
            'displayName' => 'Documentation',
            'url' => 'https://example.test/mcp',
            'headersRefreshTtlMs' => 5000,
        ]);
        $sandbox = SandboxConfig::fromArray([
            'enabled' => true,
            'userPolicy' => [
                'filesystem' => ['readwritePaths' => ['/tmp/project']],
                'network' => ['allowedHosts' => ['example.test'], 'blockedHosts' => ['evil.test']],
            ],
        ]);

        expect($server->toArray()['headersRefreshTtlMs'])->toBe(5000)
            ->and($sandbox->userPolicy)->toBeInstanceOf(SandboxConfigUserPolicy::class)
            ->and($sandbox->userPolicy->filesystem)->toBeInstanceOf(SandboxConfigUserPolicyFilesystem::class)
            ->and($sandbox->userPolicy->network)->toBeInstanceOf(SandboxConfigUserPolicyNetwork::class)
            ->and($sandbox->toArray()['userPolicy']['network']['blockedHosts'])->toBe(['evil.test']);
    });

    it('exposes adaptive thinking support for model metadata', function () {
        $capabilities = ModelCapabilities::fromArray([
            'supports' => ['adaptive_thinking' => 'adaptive_only'],
            'limits' => [],
        ]);

        expect($capabilities->adaptiveThinkingSupport())
            ->toBe(AdaptiveThinkingSupport::ADAPTIVE_ONLY);
    });

    it('round trips mode-set, shutdown, and sandbox slash-command results', function () {
        $mode = ModeSetResult::fromArray([
            'status' => 'applied',
            'modelChanged' => false,
            'modeApplied' => true,
            'armInteractiveContinuation' => true,
        ]);
        $shutdown = ShutdownRequest::fromArray(['detachSessionEndHooks' => true]);
        $command = SlashCommandTextResult::fromArray([
            'kind' => 'text',
            'text' => 'Sandbox disabled for this session',
            'sandboxSessionChange' => 'disabled',
        ]);

        expect($mode->toArray()['modeApplied'])->toBeTrue()
            ->and($mode->toArray()['armInteractiveContinuation'])->toBeTrue()
            ->and($shutdown->toArray()['detachSessionEndHooks'])->toBeTrue()
            ->and($command->sandboxSessionChange)->toBe(SandboxSessionChange::DISABLED)
            ->and($command->toArray()['sandboxSessionChange'])->toBe('disabled');
    });
});
