<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileBytesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileBytesRequest;
use Revolution\Copilot\Types\Rpc\SkillProviderListRequest;
use Revolution\Copilot\Types\Rpc\SkillProviderReadRequest;
use Revolution\Copilot\Types\SkillProviderCallOptions;

describe('session-scoped providers', function () {
    it('lists and reads skills through the registered provider', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $session = new Session('session-1', $client);
        $session->registerSkillProvider([
            'listSkills' => function (SkillProviderCallOptions $options) {
                expect($options->signal)->toBeInstanceOf(CancellationToken::class);

                return [['name' => 'deploy', 'description' => 'Deploy safely']];
            },
            'readSkill' => function (string $name, SkillProviderCallOptions $options) {
                expect($name)->toBe('deploy')
                    ->and($options->signal->isCancellationRequested())->toBeFalse();

                return "# Deploy\nUse the safe deployment procedure.";
            },
        ]);

        expect($session->handleSkillProviderList(new SkillProviderListRequest('session-1'), new CancellationToken)->toArray())
            ->toBe(['skills' => [['name' => 'deploy', 'description' => 'Deploy safely']]]);

        expect($session->handleSkillProviderRead(new SkillProviderReadRequest('session-1', 'deploy'), new CancellationToken)->toArray())
            ->toBe(['markdown' => "# Deploy\nUse the safe deployment procedure."]);
    });

    it('reports skill-provider callback cancellation as JSON-RPC cancellation', function () {
        $session = new Session('session-1', Mockery::mock(JsonRpcClient::class));
        $session->registerSkillProvider([
            'listSkills' => function (SkillProviderCallOptions $options) {
                $options->signal->cancel();

                return [];
            },
            'readSkill' => fn () => null,
        ]);

        expect(fn () => $session->handleSkillProviderList(
            new SkillProviderListRequest('session-1'),
            new CancellationToken,
        ))->toThrow(JsonRpcException::class, 'Skill provider list cancelled');
    });

    it('round-trips exact binary content through base64 RPC payloads', function () {
        $written = null;
        $session = new Session('session-1', Mockery::mock(JsonRpcClient::class));
        $session->registerSessionFsProvider([
            'readFile' => fn () => '',
            'writeFile' => fn () => null,
            'readFileBytes' => fn (string $path) => "\x00\xFFbinary",
            'writeFileBytes' => function (string $path, string $bytes) use (&$written) {
                $written = [$path, $bytes];
            },
        ]);

        $read = $session->handleSessionFsReadFileBytes(new SessionFsReadFileBytesRequest(
            path: '/tmp/binary.dat',
            sessionId: 'session-1',
        ));
        $write = $session->handleSessionFsWriteFileBytes(new SessionFsWriteFileBytesRequest(
            content: base64_encode("\x00\xFFbinary"),
            path: '/tmp/output.dat',
            sessionId: 'session-1',
        ));

        expect($read['content'])->toBe(base64_encode("\x00\xFFbinary"))
            ->and($write)->toBe([])
            ->and($written)->toBe(['/tmp/output.dat', "\x00\xFFbinary"]);
    });

    it('returns a structured error for malformed binary write payloads', function () {
        $session = new Session('session-1', Mockery::mock(JsonRpcClient::class));
        $session->registerSessionFsProvider([
            'readFile' => fn () => '',
            'writeFile' => fn () => null,
            'readFileBytes' => fn () => '',
            'writeFileBytes' => fn () => null,
        ]);

        $result = $session->handleSessionFsWriteFileBytes(new SessionFsWriteFileBytesRequest(
            content: 'not-valid-base64!',
            path: '/tmp/output.dat',
            sessionId: 'session-1',
        ));

        expect($result['error']['code'])->toBe('UNKNOWN');
    });

    it('maps missing SessionFs files to ENOENT errors', function () {
        $session = new Session('session-1', Mockery::mock(JsonRpcClient::class));
        $session->registerSessionFsProvider([
            'readFile' => fn () => throw new RuntimeException('file not found', 2),
            'writeFile' => fn () => null,
        ]);

        $result = $session->handleSessionFsReadFile(new SessionFsReadFileRequest(
            path: '/missing.txt',
            sessionId: 'session-1',
        ));

        expect($result['error']['code'])->toBe('ENOENT')
            ->and($result['error']['message'])->toBe('file not found');
    });

    it('releases provider callbacks when the session disconnects', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('session.detach', ['sessionId' => 'session-1'])
            ->andReturn(['success' => true]);

        $session = new Session('session-1', $client);
        $session->registerSkillProvider([
            'listSkills' => fn () => [],
            'readSkill' => fn () => null,
        ]);
        $session->registerSessionFsProvider([
            'readFile' => fn () => '',
            'writeFile' => fn () => null,
        ]);

        $session->disconnect();

        expect(fn () => $session->handleSkillProviderList(
            new SkillProviderListRequest('session-1'),
            new CancellationToken,
        ))->toThrow(JsonRpcException::class, 'No skill provider configured');

        expect(fn () => $session->handleSessionFsReadFile(new SessionFsReadFileRequest(
            path: '/file.txt',
            sessionId: 'session-1',
        )))->toThrow(JsonRpcException::class, 'No SessionFs provider configured');
    });
});

describe('custom apply_patch tool arguments', function () {
    it('normalizes string-schema override arguments from strings and input objects', function () {
        $session = new Session('session-1', Mockery::mock(JsonRpcClient::class));
        $received = [];
        $session->registerTools([[
            'name' => 'apply_patch',
            'overridesBuiltInTool' => true,
            'parameters' => ['type' => 'string'],
            'handler' => function (string $arguments) use (&$received) {
                $received[] = $arguments;

                return 'ok';
            },
        ]]);
        $handler = $session->getToolHandler('apply_patch');

        expect($handler('*** Begin Patch', []))->toBe('ok')
            ->and($handler(['input' => '*** End Patch'], []))->toBe('ok')
            ->and($received)->toBe(['*** Begin Patch', '*** End Patch']);
    });

    it('rejects invalid arguments for string-schema apply_patch overrides', function () {
        $session = new Session('session-1', Mockery::mock(JsonRpcClient::class));
        $session->registerTools([[
            'name' => 'apply_patch',
            'overridesBuiltInTool' => true,
            'parameters' => ['type' => 'string'],
            'handler' => fn (string $arguments) => $arguments,
        ]]);

        expect(fn () => $session->getToolHandler('apply_patch')(['wrong' => 'shape'], []))
            ->toThrow(InvalidArgumentException::class, 'requires a string input');
    });
});
