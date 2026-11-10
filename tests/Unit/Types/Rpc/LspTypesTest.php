<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\LspConfigurationProblem;
use Revolution\Copilot\Types\Rpc\LspListRequest;
use Revolution\Copilot\Types\Rpc\LspListResult;
use Revolution\Copilot\Types\Rpc\LspProbeExitStatus;
use Revolution\Copilot\Types\Rpc\LspServerSummary;
use Revolution\Copilot\Types\Rpc\LspWarmupError;
use Revolution\Copilot\Types\Rpc\SessionLspDiagnoseRequest;
use Revolution\Copilot\Types\Rpc\SessionLspDiagnoseResult;
use Revolution\Copilot\Types\Rpc\SessionLspListResult;
use Revolution\Copilot\Types\Rpc\SessionLspSetRequest;
use Revolution\Copilot\Types\Rpc\SessionLspStartResult;

describe('LSP RPC types', function () {
    it('round trips the process-wide working-directory request', function () {
        $request = LspListRequest::fromArray(['workingDirectory' => '/workspace']);

        expect($request->workingDirectory)->toBe('/workspace')
            ->and($request->toArray())->toBe(['workingDirectory' => '/workspace']);
    });

    it('round trips inventories with typed servers and project diagnostics', function () {
        $server = [
            'id' => 'php',
            'fileExtensions' => ['php' => 'php'],
            'disabled' => false,
            'rootUri' => 'src',
            'requestTimeoutMs' => 10.0,
            'initializationTimeoutMs' => 20.0,
            'spawnTimeoutMs' => 30.0,
            'warmupTimeoutMs' => 40.0,
            'sourcePlugin' => 'php-tools',
            'launchDescription' => 'php-language-server',
        ];
        $payload = [
            'configs' => [$server],
            'userServers' => [$server],
            'pluginServers' => [],
            'projectServers' => [],
            'userConfigPath' => '/home/user/.config/copilot/lsp.json',
            'projectConfigPath' => '/workspace/.github/lsp.json',
            'userConfigError' => 'user config warning',
            'projectProblems' => [[
                'path' => '/workspace/.github/lsp.json',
                'error' => 'invalid server entry',
            ]],
            'projectInspectionError' => 'project config unreadable',
            'userDisabledServers' => ['rust'],
            'warnings' => ['configuration warning'],
            'pluginWarnings' => ['plugin warning'],
        ];

        $sessionResult = SessionLspListResult::fromArray($payload);
        $serverResult = LspListResult::fromArray($payload);

        expect($sessionResult->configs[0])->toBeInstanceOf(LspServerSummary::class)
            ->and($sessionResult->configs[0]->requestTimeoutMs)->toBe(10.0)
            ->and($sessionResult->projectProblems[0])->toBeInstanceOf(LspConfigurationProblem::class)
            ->and($sessionResult->projectProblems[0]->error)->toBe('invalid server entry')
            ->and($sessionResult->toArray())->toBe($payload)
            ->and($serverResult->toArray())->toBe($payload);
    });

    it('round trips warmup results and individual errors', function () {
        $payload = [
            'servers' => ['php'],
            'errors' => [['serverId' => 'rust', 'error' => 'binary not found']],
        ];
        $result = SessionLspStartResult::fromArray($payload);

        expect($result->errors[0])->toBeInstanceOf(LspWarmupError::class)
            ->and($result->errors[0]->serverId)->toBe('rust')
            ->and($result->toArray())->toBe($payload);
    });

    it('parses diagnosis requests and defaults a missing immediate exit to null', function () {
        $request = SessionLspDiagnoseRequest::fromArray(['serverId' => 'php']);
        $result = SessionLspDiagnoseResult::fromArray([
            'status' => 'unavailable',
            'availableServers' => ['php'],
            'pid' => null,
            'spawnTimeMs' => 0,
            'sandboxed' => false,
        ]);

        expect($request->toArray())->toBe(['serverId' => 'php'])
            ->and($result->immediateExit)->toBeNull()
            ->and($result->pid)->toBeNull()
            ->and($result->spawnTimeMs)->toBe(0.0);
    });

    it('round trips exit information and session server toggles', function () {
        $exitPayload = ['code' => null, 'signal' => 'SIGTERM', 'error' => 'probe timed out'];
        $exit = LspProbeExitStatus::fromArray($exitPayload);
        $togglePayload = ['serverId' => 'php', 'enabled' => true];
        $toggle = SessionLspSetRequest::fromArray($togglePayload);

        expect($exit)->toBeInstanceOf(LspProbeExitStatus::class)
            ->and($exit->toArray())->toBe($exitPayload)
            ->and($toggle->toArray())->toBe($togglePayload);
    });
});
