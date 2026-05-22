<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\BaseHookInput;
use Revolution\Copilot\Types\Hooks\PreMcpToolCallHookInput;

describe('PreMcpToolCallHookInput', function () {
    it('can be created with all required fields', function () {
        $input = new PreMcpToolCallHookInput(
            sessionId: 'session-abc',
            timestamp: 1706600000,
            workingDirectory: '/home/user/project',
            serverName: 'filesystem',
            toolName: 'read_file',
            arguments: ['path' => '/test.txt'],
        );

        expect($input->sessionId)->toBe('session-abc')
            ->and($input->timestamp)->toBe(1706600000)
            ->and($input->workingDirectory)->toBe('/home/user/project')
            ->and($input->serverName)->toBe('filesystem')
            ->and($input->toolName)->toBe('read_file')
            ->and($input->arguments)->toBe(['path' => '/test.txt'])
            ->and($input->toolCallId)->toBeNull()
            ->and($input->_meta)->toBeNull();
    });

    it('can be created with optional fields', function () {
        $input = new PreMcpToolCallHookInput(
            sessionId: 'session-xyz',
            timestamp: 1706600000,
            workingDirectory: '/var/www',
            serverName: 'github',
            toolName: 'list_repos',
            arguments: ['org' => 'acme'],
            toolCallId: 'call-123',
            _meta: ['traceId' => 'trace-456'],
        );

        expect($input->sessionId)->toBe('session-xyz')
            ->and($input->serverName)->toBe('github')
            ->and($input->toolName)->toBe('list_repos')
            ->and($input->toolCallId)->toBe('call-123')
            ->and($input->_meta)->toBe(['traceId' => 'trace-456']);
    });

    it('can be created from array', function () {
        $input = PreMcpToolCallHookInput::fromArray([
            'sessionId' => 'session-abc',
            'timestamp' => 1706600000,
            'workingDirectory' => '/tmp',
            'serverName' => 'database',
            'toolName' => 'query',
            'arguments' => ['sql' => 'SELECT * FROM users'],
            'toolCallId' => 'call-789',
            '_meta' => ['userId' => '42'],
        ]);

        expect($input->sessionId)->toBe('session-abc')
            ->and($input->workingDirectory)->toBe('/tmp')
            ->and($input->serverName)->toBe('database')
            ->and($input->toolName)->toBe('query')
            ->and($input->arguments)->toBe(['sql' => 'SELECT * FROM users'])
            ->and($input->toolCallId)->toBe('call-789')
            ->and($input->_meta)->toBe(['userId' => '42']);
    });

    it('can be created from array with defaults', function () {
        $input = PreMcpToolCallHookInput::fromArray([]);

        expect($input->sessionId)->toBe('')
            ->and($input->timestamp)->toBe(0)
            ->and($input->workingDirectory)->toBe('')
            ->and($input->serverName)->toBe('')
            ->and($input->toolName)->toBe('')
            ->and($input->arguments)->toBeNull()
            ->and($input->toolCallId)->toBeNull()
            ->and($input->_meta)->toBeNull();
    });

    it('can convert to array excluding null optional fields', function () {
        $input = new PreMcpToolCallHookInput(
            sessionId: 'session-abc',
            timestamp: 1706600000,
            workingDirectory: '/tmp',
            serverName: 'api',
            toolName: 'fetch',
            arguments: ['url' => 'https://example.com'],
        );

        expect($input->toArray())->toBe([
            'sessionId' => 'session-abc',
            'timestamp' => 1706600000,
            'workingDirectory' => '/tmp',
            'serverName' => 'api',
            'toolName' => 'fetch',
            'arguments' => ['url' => 'https://example.com'],
        ]);
    });

    it('can convert to array including optional fields', function () {
        $input = new PreMcpToolCallHookInput(
            sessionId: 'session-abc',
            timestamp: 1706600000,
            workingDirectory: '/tmp',
            serverName: 'api',
            toolName: 'fetch',
            arguments: ['url' => 'https://example.com'],
            toolCallId: 'call-999',
            _meta: ['retry' => true],
        );

        expect($input->toArray())->toBe([
            'sessionId' => 'session-abc',
            'timestamp' => 1706600000,
            'workingDirectory' => '/tmp',
            'serverName' => 'api',
            'toolName' => 'fetch',
            'arguments' => ['url' => 'https://example.com'],
            'toolCallId' => 'call-999',
            '_meta' => ['retry' => true],
        ]);
    });

    it('extends BaseHookInput', function () {
        $input = new PreMcpToolCallHookInput(
            sessionId: '',
            timestamp: 0,
            workingDirectory: '',
            serverName: '',
            toolName: '',
            arguments: null,
        );

        expect($input)->toBeInstanceOf(BaseHookInput::class);
    });
});
