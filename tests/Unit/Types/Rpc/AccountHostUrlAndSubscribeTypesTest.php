<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AuthInfoType;
use Revolution\Copilot\Types\Rpc\AccountGetHostUrlRequest;
use Revolution\Copilot\Types\Rpc\AccountGetHostUrlResult;
use Revolution\Copilot\Types\Rpc\AccountSubscribeCopilotFreeRequest;
use Revolution\Copilot\Types\Rpc\AccountSubscribeCopilotFreeResult;
use Revolution\Copilot\Types\Rpc\AuthIdentity;

describe('AccountGetHostUrlRequest', function () {
    it('round trips configured host names', function () {
        $data = [
            'copilotGhHost' => 'github.example.com',
            'ghHost' => 'github.com',
        ];

        expect(AccountGetHostUrlRequest::fromArray($data)->toArray())->toBe($data);
    });

    it('omits unset host names', function () {
        expect((new AccountGetHostUrlRequest)->toArray())->toBe([]);
    });
});

describe('AccountGetHostUrlResult', function () {
    it('round trips the resolved URL', function () {
        $data = ['url' => 'https://github.example.com'];
        $result = AccountGetHostUrlResult::fromArray($data);

        expect($result->url)->toBe($data['url'])
            ->and($result->toArray())->toBe($data);
    });
});

describe('AuthIdentity', function () {
    it('round trips identity metadata without credentials', function () {
        $data = [
            'type' => 'token',
            'host' => 'https://github.com',
            'login' => 'octocat',
            'envVar' => 'GH_TOKEN',
            'registrationId' => 'registration-123',
            'copilotUser' => ['plan' => 'free'],
        ];
        $identity = AuthIdentity::fromArray($data);

        expect($identity->type)->toBe(AuthInfoType::TOKEN)
            ->and($identity->toArray())->toBe($data);
    });

    it('omits optional identity metadata when unset', function () {
        $identity = new AuthIdentity(type: AuthInfoType::TOKEN, host: 'https://github.com');

        expect($identity->toArray())->toBe([
            'type' => 'token',
            'host' => 'https://github.com',
        ]);
    });
});

describe('AccountSubscribeCopilotFreeRequest', function () {
    it('hydrates and round trips the nested identity', function () {
        $data = [
            'identity' => [
                'type' => 'token',
                'host' => 'https://github.com',
                'login' => 'octocat',
            ],
        ];
        $request = AccountSubscribeCopilotFreeRequest::fromArray($data);

        expect($request->identity)->toBeInstanceOf(AuthIdentity::class)
            ->and($request->toArray())->toBe($data);
    });
});

describe('AccountSubscribeCopilotFreeResult', function () {
    it('round trips the outcome and optional details', function () {
        $data = [
            'kind' => 'subscribed',
            'status' => 'already_subscribed',
            'message' => 'Already enrolled',
        ];
        $result = AccountSubscribeCopilotFreeResult::fromArray($data);

        expect($result->toArray())->toBe($data);
    });

    it('omits optional details when unset', function () {
        expect((new AccountSubscribeCopilotFreeResult(kind: 'no-token'))->toArray())
            ->toBe(['kind' => 'no-token']);
    });
});
