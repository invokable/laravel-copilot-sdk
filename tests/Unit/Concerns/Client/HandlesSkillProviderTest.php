<?php

declare(strict_types=1);

use Revolution\Copilot\Client;
use Revolution\Copilot\Contracts\SkillProvider;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\SkillProviderCallOptions;

function createClientWithSkillProviderSession(Session $session): Client
{
    $client = new Client;
    (new ReflectionProperty(Client::class, 'sessions'))->setValue($client, [$session->id() => $session]);

    return $client;
}

describe('HandlesSkillProvider', function () {
    it('routes skill list and read requests to the matching session', function () {
        $session = new Session('session-123', Mockery::mock(JsonRpcClient::class));
        $session->registerSkillProvider([
            'listSkills' => fn (SkillProviderCallOptions $options): array => [['name' => 'deploy']],
            'readSkill' => fn (string $name, SkillProviderCallOptions $options): string => "# {$name}",
        ]);
        $client = createClientWithSkillProviderSession($session);
        $token = new CancellationToken;

        $list = (new ReflectionMethod(Client::class, 'handleSkillProviderList'))
            ->invoke($client, ['sessionId' => 'session-123'], $token);
        $read = (new ReflectionMethod(Client::class, 'handleSkillProviderRead'))
            ->invoke($client, ['sessionId' => 'session-123', 'name' => 'deploy'], $token);

        expect($list)->toBe(['skills' => [['name' => 'deploy']]])
            ->and($read)->toBe(['markdown' => '# deploy']);
    });

    it('throws when a request references a missing session', function () {
        $client = new Client;
        $list = new ReflectionMethod(Client::class, 'handleSkillProviderList');
        $read = new ReflectionMethod(Client::class, 'handleSkillProviderRead');

        expect(fn () => $list->invoke($client, ['sessionId' => 'missing'], new CancellationToken))
            ->toThrow(RuntimeException::class, 'Session not found: missing')
            ->and(fn () => $read->invoke($client, ['sessionId' => 'missing', 'name' => 'deploy'], new CancellationToken))
            ->toThrow(RuntimeException::class, 'Session not found: missing');
    });

    it('accepts null, interface implementations, and callable arrays as providers', function () {
        $client = new Client;
        $validate = new ReflectionMethod(Client::class, 'validateSkillProvider');

        expect(fn () => $validate->invoke($client, null))
            ->not->toThrow(InvalidArgumentException::class)
            ->and(fn () => $validate->invoke($client, Mockery::mock(SkillProvider::class)))
            ->not->toThrow(InvalidArgumentException::class)
            ->and(fn () => $validate->invoke($client, [
                'listSkills' => fn (): array => [],
                'readSkill' => fn (): null => null,
            ]))
            ->not->toThrow(InvalidArgumentException::class);
    });

    it('rejects providers without both callable methods', function () {
        $client = new Client;
        $validate = new ReflectionMethod(Client::class, 'validateSkillProvider');

        expect(fn () => $validate->invoke($client, ['listSkills' => fn (): array => []]))
            ->toThrow(InvalidArgumentException::class, 'skillProvider must implement SkillProvider');
    });
});
