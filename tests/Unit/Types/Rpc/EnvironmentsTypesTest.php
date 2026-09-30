<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\EnvironmentKind;
use Revolution\Copilot\Types\Rpc\EnvironmentCapabilities;
use Revolution\Copilot\Types\Rpc\EnvironmentsDeleteRequest;
use Revolution\Copilot\Types\Rpc\EnvironmentsDeleteResult;
use Revolution\Copilot\Types\Rpc\EnvironmentsGetRequest;
use Revolution\Copilot\Types\Rpc\EnvironmentsGetResult;
use Revolution\Copilot\Types\Rpc\EnvironmentsListRequest;
use Revolution\Copilot\Types\Rpc\EnvironmentsListResult;
use Revolution\Copilot\Types\Rpc\GitHubEnvironment;
use Revolution\Copilot\Types\Rpc\ProviderWithdrawRequest;
use Revolution\Copilot\Types\Rpc\ProviderWithdrawResult;

describe('Environments types', function () {
    it('roundtrips list request', function () {
        $req = EnvironmentsListRequest::fromArray(['kind' => 'user-local', 'status' => 'online']);

        expect($req->kind)->toBe(EnvironmentKind::USER_LOCAL)
            ->and($req->toArray())->toBe(['kind' => 'user-local', 'status' => 'online'])
            ->and((new EnvironmentsListRequest)->toArray())->toBe([]);
    });

    it('roundtrips get/delete requests and delete result', function () {
        expect(EnvironmentsGetRequest::fromArray(['environmentId' => 'e1'])->toArray())->toBe(['environmentId' => 'e1'])
            ->and(EnvironmentsDeleteRequest::fromArray(['environmentId' => 'e2'])->toArray())->toBe(['environmentId' => 'e2'])
            ->and(EnvironmentsDeleteResult::fromArray([])->toArray())->toBe([]);
    });

    it('roundtrips environment results', function () {
        $data = [
            'id' => 'e1',
            'kind' => 'managed-cca',
            'name' => 'Env',
            'status' => 'online',
            'capabilities' => ['features' => ['a'], 'ahpVersion' => '1', 'currentSessions' => 1, 'maxSessions' => 5],
            'labels' => ['k' => 'v'],
            'ownerId' => 'o',
        ];

        $env = GitHubEnvironment::fromArray($data);
        expect($env->capabilities)->toBeInstanceOf(EnvironmentCapabilities::class)
            ->and($env->toArray())->toBe($data)
            ->and(EnvironmentsGetResult::fromArray(['environment' => $data])->toArray())->toBe(['environment' => $data])
            ->and(EnvironmentsListResult::fromArray(['environments' => [$data]])->toArray())->toBe(['environments' => [$data]])
            ->and((new EnvironmentsListResult)->environments)->toBe([]);
    });
});

describe('Provider withdraw types', function () {
    it('roundtrips request and result', function () {
        expect(ProviderWithdrawRequest::fromArray(['models' => ['p/m']])->toArray())->toBe(['models' => ['p/m']]);

        $result = ProviderWithdrawResult::fromArray(['providersRemoved' => ['p'], 'withdrawn' => ['p/m'], 'modelDeselected' => true]);
        expect($result->toArray())->toBe(['providersRemoved' => ['p'], 'withdrawn' => ['p/m'], 'modelDeselected' => true])
            ->and((new ProviderWithdrawResult)->toArray())->toBe(['providersRemoved' => [], 'withdrawn' => []]);
    });
});
