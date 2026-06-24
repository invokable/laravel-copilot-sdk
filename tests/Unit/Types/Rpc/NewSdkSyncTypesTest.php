<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AuthInfoType;
use Revolution\Copilot\Enums\SectionOverrideAction;
use Revolution\Copilot\Enums\SystemMessageSection;
use Revolution\Copilot\Types\Rpc\AccountAllUsers;
use Revolution\Copilot\Types\Rpc\AccountGetCurrentAuthResult;
use Revolution\Copilot\Types\Rpc\AccountLoginRequest;
use Revolution\Copilot\Types\Rpc\AccountLoginResult;
use Revolution\Copilot\Types\Rpc\AccountLogoutRequest;
use Revolution\Copilot\Types\Rpc\AccountLogoutResult;
use Revolution\Copilot\Types\Rpc\AuthInfo;
use Revolution\Copilot\Types\Rpc\ProviderAddRequest;
use Revolution\Copilot\Types\Rpc\ProviderAddResult;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;

describe('SystemMessageSection preamble', function () {
    it('has preamble case', function () {
        expect(SystemMessageSection::PREAMBLE->value)->toBe('preamble');
    });

    it('has updated identity description', function () {
        expect(SystemMessageSection::IDENTITY->value)->toBe('identity');
        $descriptions = SystemMessageSection::descriptions();
        expect($descriptions['preamble'])->toBe('Agent identity preamble and mode statement')
            ->and($descriptions['identity'])->toContain('Section group');
    });

    it('has 12 cases', function () {
        expect(SystemMessageSection::cases())->toHaveCount(12);
    });

    it('preamble is in descriptions', function () {
        expect(SystemMessageSection::descriptions())->toHaveKey('preamble');
    });
});

describe('SectionOverrideAction preserve', function () {
    it('has preserve case', function () {
        expect(SectionOverrideAction::PRESERVE->value)->toBe('preserve');
    });

    it('has 6 cases', function () {
        expect(SectionOverrideAction::cases())->toHaveCount(6);
    });

    it('can be created from string', function () {
        expect(SectionOverrideAction::from('preserve'))->toBe(SectionOverrideAction::PRESERVE);
    });
});

describe('expAssignments in SessionConfig', function () {
    it('accepts expAssignments', function () {
        $config = new SessionConfig(
            model: 'gpt-5',
            expAssignments: ['flag_a' => true, 'flag_b' => 'value'],
        );

        expect($config->expAssignments)->toBe(['flag_a' => true, 'flag_b' => 'value']);
    });

    it('defaults expAssignments to null', function () {
        $config = new SessionConfig(model: 'gpt-5');
        expect($config->expAssignments)->toBeNull();
    });

    it('roundtrips expAssignments', function () {
        $config = SessionConfig::fromArray([
            'model' => 'gpt-5',
            'expAssignments' => ['ff_new_feature' => true],
        ]);

        expect($config->expAssignments)->toBe(['ff_new_feature' => true]);
        expect($config->toArray())->toHaveKey('expAssignments');
    });

    it('omits expAssignments when null', function () {
        $config = new SessionConfig(model: 'gpt-5');
        expect($config->toArray())->not->toHaveKey('expAssignments');
    });
});

describe('expAssignments in ResumeSessionConfig', function () {
    it('accepts expAssignments', function () {
        $config = new ResumeSessionConfig(
            expAssignments: ['flag' => 'val'],
        );

        expect($config->expAssignments)->toBe(['flag' => 'val']);
    });

    it('roundtrips expAssignments', function () {
        $config = ResumeSessionConfig::fromArray([
            'expAssignments' => ['ff_abc' => false],
        ]);

        expect($config->expAssignments)->toBe(['ff_abc' => false]);
        expect($config->toArray())->toHaveKey('expAssignments');
    });

    it('omits expAssignments when null', function () {
        $config = new ResumeSessionConfig;
        expect($config->toArray())->not->toHaveKey('expAssignments');
    });
});

describe('AccountLoginRequest', function () {
    it('can be created with required fields', function () {
        $request = new AccountLoginRequest(
            host: 'https://github.com',
            login: 'octocat',
            token: 'gho_secret123',
        );

        expect($request->host)->toBe('https://github.com')
            ->and($request->login)->toBe('octocat')
            ->and($request->token)->toBe('gho_secret123');
    });

    it('roundtrips via array', function () {
        $data = ['host' => 'https://github.com', 'login' => 'octocat', 'token' => 'gho_token'];
        $request = AccountLoginRequest::fromArray($data);

        expect($request->toArray())->toBe($data);
    });
});

describe('AccountLoginResult', function () {
    it('can be created from array', function () {
        $result = AccountLoginResult::fromArray(['storedInVault' => true]);
        expect($result->storedInVault)->toBeTrue();
    });

    it('defaults storedInVault to false', function () {
        $result = AccountLoginResult::fromArray([]);
        expect($result->storedInVault)->toBeFalse();
    });

    it('roundtrips via array', function () {
        $result = new AccountLoginResult(storedInVault: true);
        expect($result->toArray())->toBe(['storedInVault' => true]);
    });
});

describe('AccountLogoutRequest', function () {
    it('can be created from array', function () {
        $request = AccountLogoutRequest::fromArray([
            'authInfo' => [
                'host' => 'https://github.com',
                'type' => 'token',
                'token' => 'gho_token',
            ],
        ]);

        expect($request->authInfo)->toBeInstanceOf(AuthInfo::class)
            ->and($request->authInfo->host)->toBe('https://github.com');
    });

    it('roundtrips via array', function () {
        $authInfo = new AuthInfo(host: 'https://github.com', type: AuthInfoType::TOKEN, token: 'gho_token');
        $request = new AccountLogoutRequest(authInfo: $authInfo);
        $array = $request->toArray();

        expect($array)->toHaveKey('authInfo')
            ->and($array['authInfo']['host'])->toBe('https://github.com');
    });
});

describe('AccountLogoutResult', function () {
    it('can be created from array', function () {
        $result = AccountLogoutResult::fromArray(['hasMoreUsers' => true]);
        expect($result->hasMoreUsers)->toBeTrue();
    });

    it('defaults hasMoreUsers to false', function () {
        $result = AccountLogoutResult::fromArray([]);
        expect($result->hasMoreUsers)->toBeFalse();
    });

    it('roundtrips via array', function () {
        $result = new AccountLogoutResult(hasMoreUsers: false);
        expect($result->toArray())->toBe(['hasMoreUsers' => false]);
    });
});

describe('AccountGetCurrentAuthResult', function () {
    it('can be created with all fields', function () {
        $result = AccountGetCurrentAuthResult::fromArray([
            'authErrors' => ['error1', 'error2'],
            'authInfo' => ['host' => 'https://github.com', 'type' => 'token'],
        ]);

        expect($result->authErrors)->toBe(['error1', 'error2'])
            ->and($result->authInfo)->toBeInstanceOf(AuthInfo::class);
    });

    it('defaults to nulls', function () {
        $result = new AccountGetCurrentAuthResult;
        expect($result->authErrors)->toBeNull()
            ->and($result->authInfo)->toBeNull();
    });

    it('omits nulls in toArray', function () {
        $result = new AccountGetCurrentAuthResult;
        expect($result->toArray())->toBe([]);
    });

    it('roundtrips auth errors', function () {
        $result = AccountGetCurrentAuthResult::fromArray(['authErrors' => ['err']]);
        expect($result->toArray()['authErrors'])->toBe(['err']);
    });
});

describe('AccountAllUsers', function () {
    it('can be created from array', function () {
        $user = AccountAllUsers::fromArray([
            'authInfo' => ['host' => 'https://github.com', 'type' => 'token'],
            'token' => 'gho_token',
        ]);

        expect($user->authInfo)->toBeInstanceOf(AuthInfo::class)
            ->and($user->token)->toBe('gho_token');
    });

    it('token is optional', function () {
        $user = AccountAllUsers::fromArray([
            'authInfo' => ['host' => 'https://github.com', 'type' => 'token'],
        ]);

        expect($user->token)->toBeNull();
        expect($user->toArray())->not->toHaveKey('token');
    });
});

describe('ProviderAddResult', function () {
    it('can be created from array', function () {
        $result = ProviderAddResult::fromArray(['models' => [['id' => 'gpt-5']]]);
        expect($result->models)->toBe([['id' => 'gpt-5']]);
    });

    it('defaults models to empty array', function () {
        $result = ProviderAddResult::fromArray([]);
        expect($result->models)->toBe([]);
    });

    it('roundtrips via array', function () {
        $result = new ProviderAddResult(models: []);
        expect($result->toArray())->toBe(['models' => []]);
    });
});

describe('ProviderAddRequest', function () {
    it('can be created with defaults', function () {
        $request = new ProviderAddRequest;
        expect($request->models)->toBeNull()
            ->and($request->providers)->toBeNull();
    });

    it('roundtrips without data', function () {
        $request = ProviderAddRequest::fromArray([]);
        expect($request->toArray())->toBe([]);
    });

    it('roundtrips from array', function () {
        $request = ProviderAddRequest::fromArray([
            'models' => [['id' => 'my-model', 'provider' => 'my-provider']],
            'providers' => [['name' => 'my-provider', 'baseUrl' => 'https://api.example.com']],
        ]);

        expect($request->models)->toHaveCount(1)
            ->and($request->providers)->toHaveCount(1);

        $array = $request->toArray();
        expect($array)->toHaveKey('models')
            ->and($array)->toHaveKey('providers');
    });
});
