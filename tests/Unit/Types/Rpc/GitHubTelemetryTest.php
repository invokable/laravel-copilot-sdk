<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\GitHubTelemetryClientInfo;
use Revolution\Copilot\Types\Rpc\GitHubTelemetryEvent;
use Revolution\Copilot\Types\Rpc\GitHubTelemetryNotification;

describe('GitHubTelemetryClientInfo', function () {
    it('can be created with required fields', function () {
        $info = new GitHubTelemetryClientInfo(
            cliVersion: '1.0.0',
            osPlatform: 'linux',
            osVersion: '5.15.0',
            osArch: 'x64',
            nodeVersion: '20.0.0',
        );

        expect($info->cliVersion)->toBe('1.0.0')
            ->and($info->osPlatform)->toBe('linux')
            ->and($info->osVersion)->toBe('5.15.0')
            ->and($info->osArch)->toBe('x64')
            ->and($info->nodeVersion)->toBe('20.0.0')
            ->and($info->copilotPlan)->toBeNull()
            ->and($info->isStaff)->toBeNull();
    });

    it('can be created from array', function () {
        $info = GitHubTelemetryClientInfo::fromArray([
            'cli_version' => '1.0.5',
            'os_platform' => 'darwin',
            'os_version' => '14.0',
            'os_arch' => 'arm64',
            'node_version' => '20.11.0',
            'copilot_plan' => 'business',
            'is_staff' => true,
        ]);

        expect($info->cliVersion)->toBe('1.0.5')
            ->and($info->osPlatform)->toBe('darwin')
            ->and($info->osArch)->toBe('arm64')
            ->and($info->copilotPlan)->toBe('business')
            ->and($info->isStaff)->toBeTrue();
    });

    it('converts to array filtering nulls', function () {
        $info = new GitHubTelemetryClientInfo(
            cliVersion: '1.0.0',
            osPlatform: 'linux',
            osVersion: '5.15.0',
            osArch: 'x64',
            nodeVersion: '20.0.0',
        );

        $array = $info->toArray();

        expect($array)->toHaveKey('cli_version', '1.0.0')
            ->and($array)->toHaveKey('os_platform', 'linux')
            ->and($array)->not->toHaveKey('copilot_plan')
            ->and($array)->not->toHaveKey('is_staff');
    });

    it('implements Arrayable', function () {
        expect(new GitHubTelemetryClientInfo('1.0', 'linux', '5.0', 'x64', '20.0'))
            ->toBeInstanceOf(Arrayable::class);
    });
});

describe('GitHubTelemetryEvent', function () {
    it('can be created with required fields', function () {
        $event = new GitHubTelemetryEvent(
            kind: 'get_completion_with_tools_turn',
            properties: ['model' => 'gpt-4'],
            metrics: ['latency_ms' => 500.0],
        );

        expect($event->kind)->toBe('get_completion_with_tools_turn')
            ->and($event->properties)->toBe(['model' => 'gpt-4'])
            ->and($event->metrics)->toBe(['latency_ms' => 500.0])
            ->and($event->createdAt)->toBeNull()
            ->and($event->client)->toBeNull();
    });

    it('can be created from array', function () {
        $event = GitHubTelemetryEvent::fromArray([
            'kind' => 'tool_call_executed',
            'properties' => ['tool_name' => 'bash'],
            'metrics' => ['duration_ms' => 100.0],
            'created_at' => '2024-01-01T00:00:00Z',
            'session_id' => 'sess-123',
        ]);

        expect($event->kind)->toBe('tool_call_executed')
            ->and($event->properties)->toBe(['tool_name' => 'bash'])
            ->and($event->createdAt)->toBe('2024-01-01T00:00:00Z')
            ->and($event->sessionId)->toBe('sess-123');
    });

    it('can include a client info', function () {
        $event = GitHubTelemetryEvent::fromArray([
            'kind' => 'test',
            'properties' => [],
            'metrics' => [],
            'client' => [
                'cli_version' => '1.0.0',
                'os_platform' => 'linux',
                'os_version' => '5.0',
                'os_arch' => 'x64',
                'node_version' => '20.0',
            ],
        ]);

        expect($event->client)->toBeInstanceOf(GitHubTelemetryClientInfo::class)
            ->and($event->client->cliVersion)->toBe('1.0.0');
    });

    it('converts to array filtering nulls', function () {
        $event = new GitHubTelemetryEvent(
            kind: 'test_event',
            properties: ['key' => 'value'],
            metrics: ['count' => 1.0],
        );

        $array = $event->toArray();

        expect($array)->toHaveKey('kind', 'test_event')
            ->and($array)->toHaveKey('properties')
            ->and($array)->toHaveKey('metrics')
            ->and($array)->not->toHaveKey('created_at')
            ->and($array)->not->toHaveKey('client');
    });

    it('implements Arrayable', function () {
        expect(new GitHubTelemetryEvent('test', [], []))->toBeInstanceOf(Arrayable::class);
    });
});

describe('GitHubTelemetryNotification', function () {
    it('can be created with all fields', function () {
        $event = new GitHubTelemetryEvent('test', [], []);
        $notification = new GitHubTelemetryNotification(
            sessionId: 'sess-abc',
            restricted: false,
            event: $event,
        );

        expect($notification->sessionId)->toBe('sess-abc')
            ->and($notification->restricted)->toBeFalse()
            ->and($notification->event)->toBe($event);
    });

    it('can be created from array', function () {
        $notification = GitHubTelemetryNotification::fromArray([
            'sessionId' => 'sess-xyz',
            'restricted' => true,
            'event' => [
                'kind' => 'telemetry_event',
                'properties' => [],
                'metrics' => [],
            ],
        ]);

        expect($notification->sessionId)->toBe('sess-xyz')
            ->and($notification->restricted)->toBeTrue()
            ->and($notification->event)->toBeInstanceOf(GitHubTelemetryEvent::class)
            ->and($notification->event->kind)->toBe('telemetry_event');
    });

    it('accepts GitHubTelemetryEvent instance in fromArray', function () {
        $event = new GitHubTelemetryEvent('test', [], []);
        $notification = GitHubTelemetryNotification::fromArray([
            'sessionId' => 'sess-1',
            'restricted' => false,
            'event' => $event,
        ]);

        expect($notification->event)->toBe($event);
    });

    it('converts to array', function () {
        $notification = new GitHubTelemetryNotification(
            sessionId: 'sess-abc',
            restricted: false,
            event: new GitHubTelemetryEvent('test', ['k' => 'v'], ['m' => 1.0]),
        );

        $array = $notification->toArray();

        expect($array)->toHaveKey('sessionId', 'sess-abc')
            ->and($array)->toHaveKey('restricted', false)
            ->and($array)->toHaveKey('event')
            ->and($array['event'])->toHaveKey('kind', 'test');
    });

    it('implements Arrayable', function () {
        $notification = new GitHubTelemetryNotification(
            sessionId: 'sess-1',
            restricted: false,
            event: new GitHubTelemetryEvent('test', [], []),
        );

        expect($notification)->toBeInstanceOf(Arrayable::class);
    });
});
