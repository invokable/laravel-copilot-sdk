<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AuthInfoType;
use Revolution\Copilot\Enums\PermissionsSetApproveAllSource;
use Revolution\Copilot\Types\Rpc\AccountGetQuotaRequest;
use Revolution\Copilot\Types\Rpc\AuthInfo;
use Revolution\Copilot\Types\Rpc\McpConfigDisableRequest;
use Revolution\Copilot\Types\Rpc\McpConfigEnableRequest;
use Revolution\Copilot\Types\Rpc\McpOauthLoginRequest;
use Revolution\Copilot\Types\Rpc\McpOauthLoginResult;
use Revolution\Copilot\Types\Rpc\ModelsListRequest;
use Revolution\Copilot\Types\Rpc\PermissionsResetSessionApprovalsRequest;
use Revolution\Copilot\Types\Rpc\PermissionsResetSessionApprovalsResult;
use Revolution\Copilot\Types\Rpc\PermissionsSetApproveAllRequest;
use Revolution\Copilot\Types\Rpc\PermissionsSetApproveAllResult;
use Revolution\Copilot\Types\Rpc\SessionAuthStatus;
use Revolution\Copilot\Types\Rpc\SessionSetCredentialsParams;
use Revolution\Copilot\Types\Rpc\SessionSetCredentialsResult;

describe('AccountGetQuotaRequest', function () {
    it('can be created with gitHubToken', function () {
        $req = AccountGetQuotaRequest::fromArray(['gitHubToken' => 'ghs_abc123']);

        expect($req->gitHubToken)->toBe('ghs_abc123');
    });

    it('defaults to null token', function () {
        $req = AccountGetQuotaRequest::fromArray([]);

        expect($req->gitHubToken)->toBeNull();
    });

    it('serializes to array omitting null token', function () {
        $req = new AccountGetQuotaRequest;

        expect($req->toArray())->toBe([]);
    });

    it('serializes token when present', function () {
        $req = new AccountGetQuotaRequest(gitHubToken: 'ghs_xyz');

        expect($req->toArray())->toBe(['gitHubToken' => 'ghs_xyz']);
    });
});

describe('McpConfigEnableRequest', function () {
    it('can be created with names', function () {
        $req = McpConfigEnableRequest::fromArray(['names' => ['server1', 'server2']]);

        expect($req->names)->toBe(['server1', 'server2']);
    });

    it('defaults to empty names', function () {
        $req = McpConfigEnableRequest::fromArray([]);

        expect($req->names)->toBe([]);
    });

    it('serializes to array', function () {
        $req = new McpConfigEnableRequest(names: ['server1']);

        expect($req->toArray())->toBe(['names' => ['server1']]);
    });
});

describe('McpConfigDisableRequest', function () {
    it('can be created with names', function () {
        $req = McpConfigDisableRequest::fromArray(['names' => ['server1']]);

        expect($req->names)->toBe(['server1']);
    });

    it('serializes to array', function () {
        $req = new McpConfigDisableRequest(names: ['server1', 'server2']);

        expect($req->toArray())->toBe(['names' => ['server1', 'server2']]);
    });
});

describe('McpOauthLoginRequest', function () {
    it('can be created with required fields', function () {
        $req = McpOauthLoginRequest::fromArray(['serverName' => 'my-server']);

        expect($req->serverName)->toBe('my-server')
            ->and($req->callbackSuccessMessage)->toBeNull()
            ->and($req->clientName)->toBeNull()
            ->and($req->forceReauth)->toBeNull();
    });

    it('can be created with all fields', function () {
        $req = McpOauthLoginRequest::fromArray([
            'serverName' => 'my-server',
            'callbackSuccessMessage' => 'Success!',
            'clientName' => 'My App',
            'forceReauth' => true,
        ]);

        expect($req->serverName)->toBe('my-server')
            ->and($req->callbackSuccessMessage)->toBe('Success!')
            ->and($req->clientName)->toBe('My App')
            ->and($req->forceReauth)->toBeTrue();
    });

    it('serializes omitting null fields', function () {
        $req = new McpOauthLoginRequest(serverName: 'my-server');

        expect($req->toArray())->toBe(['serverName' => 'my-server']);
    });

    it('serializes all fields', function () {
        $req = new McpOauthLoginRequest(
            serverName: 'my-server',
            callbackSuccessMessage: 'Done!',
            clientName: 'Test App',
            forceReauth: false,
        );

        expect($req->toArray())->toHaveKeys(['serverName', 'callbackSuccessMessage', 'clientName', 'forceReauth']);
    });
});

describe('McpOauthLoginResult', function () {
    it('can be created with authorizationUrl', function () {
        $result = McpOauthLoginResult::fromArray(['authorizationUrl' => 'https://github.com/login/oauth/authorize?...']);

        expect($result->authorizationUrl)->toContain('github.com');
    });

    it('can be created without authorizationUrl', function () {
        $result = McpOauthLoginResult::fromArray([]);

        expect($result->authorizationUrl)->toBeNull();
    });

    it('serializes empty when no url', function () {
        $result = new McpOauthLoginResult;

        expect($result->toArray())->toBe([]);
    });
});

describe('ModelsListRequest', function () {
    it('can be created with gitHubToken', function () {
        $req = ModelsListRequest::fromArray(['gitHubToken' => 'ghs_token123']);

        expect($req->gitHubToken)->toBe('ghs_token123');
    });

    it('defaults to null token', function () {
        $req = ModelsListRequest::fromArray([]);

        expect($req->gitHubToken)->toBeNull();
    });

    it('serializes omitting null token', function () {
        $req = new ModelsListRequest;

        expect($req->toArray())->toBe([]);
    });
});

describe('PermissionsResetSessionApprovalsRequest', function () {
    it('can be created from empty array', function () {
        $req = PermissionsResetSessionApprovalsRequest::fromArray([]);

        expect($req)->toBeInstanceOf(PermissionsResetSessionApprovalsRequest::class);
    });

    it('serializes to empty array', function () {
        $req = new PermissionsResetSessionApprovalsRequest;

        expect($req->toArray())->toBe([]);
    });
});

describe('PermissionsResetSessionApprovalsResult', function () {
    it('can be created with success true', function () {
        $result = PermissionsResetSessionApprovalsResult::fromArray(['success' => true]);

        expect($result->success)->toBeTrue();
    });

    it('serializes to array', function () {
        $result = new PermissionsResetSessionApprovalsResult(success: false);

        expect($result->toArray())->toBe(['success' => false]);
    });
});

describe('PermissionsSetApproveAllRequest', function () {
    it('can be created with enabled true', function () {
        $req = PermissionsSetApproveAllRequest::fromArray(['enabled' => true]);

        expect($req->enabled)->toBeTrue();
    });

    it('can be created with enabled false', function () {
        $req = PermissionsSetApproveAllRequest::fromArray(['enabled' => false]);

        expect($req->enabled)->toBeFalse();
    });

    it('serializes to array', function () {
        $req = new PermissionsSetApproveAllRequest(enabled: true);

        expect($req->toArray())->toBe(['enabled' => true]);
    });

    it('can parse and serialize source enum', function () {
        $req = PermissionsSetApproveAllRequest::fromArray([
            'enabled' => true,
            'source' => 'rpc',
        ]);

        expect($req->source)->toBe(PermissionsSetApproveAllSource::RPC)
            ->and($req->toArray())->toBe([
                'enabled' => true,
                'source' => 'rpc',
            ]);
    });
});

describe('PermissionsSetApproveAllResult', function () {
    it('can be created with success', function () {
        $result = PermissionsSetApproveAllResult::fromArray(['success' => true]);

        expect($result->success)->toBeTrue();
    });

    it('serializes to array', function () {
        $result = new PermissionsSetApproveAllResult(success: true);

        expect($result->toArray())->toBe(['success' => true]);
    });
});

describe('SessionAuthStatus', function () {
    it('can be created with all fields', function () {
        $status = SessionAuthStatus::fromArray([
            'isAuthenticated' => true,
            'authType' => 'gh-cli',
            'copilotPlan' => 'individual_pro',
            'host' => 'https://github.com',
            'login' => 'octocat',
            'statusMessage' => 'Authenticated',
        ]);

        expect($status->isAuthenticated)->toBeTrue()
            ->and($status->authType)->toBe(AuthInfoType::GH_CLI)
            ->and($status->copilotPlan)->toBe('individual_pro')
            ->and($status->login)->toBe('octocat');
    });

    it('handles minimal data', function () {
        $status = SessionAuthStatus::fromArray(['isAuthenticated' => false]);

        expect($status->isAuthenticated)->toBeFalse()
            ->and($status->authType)->toBeNull()
            ->and($status->login)->toBeNull();
    });

    it('serializes to array', function () {
        $status = new SessionAuthStatus(
            isAuthenticated: true,
            authType: AuthInfoType::TOKEN,
            login: 'user123',
        );
        $arr = $status->toArray();

        expect($arr)->toHaveKey('isAuthenticated', true)
            ->and($arr)->toHaveKey('authType', 'token')
            ->and($arr)->toHaveKey('login', 'user123')
            ->and($arr)->not->toHaveKey('copilotPlan');
    });

    it('handles unknown authType as string', function () {
        $status = SessionAuthStatus::fromArray([
            'isAuthenticated' => true,
            'authType' => 'unknown-type',
        ]);

        expect($status->authType)->toBe('unknown-type');
    });
});

describe('AuthInfo', function () {
    it('can be created with known type', function () {
        $auth = AuthInfo::fromArray([
            'host' => 'https://github.com',
            'type' => 'user',
            'login' => 'octocat',
            'copilotUser' => ['login' => 'octocat'],
        ]);

        expect($auth->type)->toBe(AuthInfoType::USER)
            ->and($auth->host)->toBe('https://github.com')
            ->and($auth->login)->toBe('octocat')
            ->and($auth->copilotUser)->toBe(['login' => 'octocat']);
    });

    it('serializes optional values and enum type', function () {
        $auth = new AuthInfo(
            host: 'https://github.com',
            type: AuthInfoType::TOKEN,
            token: 'ghp_test',
        );

        expect($auth->toArray())->toBe([
            'host' => 'https://github.com',
            'type' => 'token',
            'token' => 'ghp_test',
        ]);
    });

    it('keeps unknown type as string', function () {
        $auth = AuthInfo::fromArray([
            'host' => 'https://example.com',
            'type' => 'custom-auth-type',
        ]);

        expect($auth->type)->toBe('custom-auth-type');
    });
});

describe('SessionSetCredentialsParams', function () {
    it('can be created without credentials', function () {
        $params = SessionSetCredentialsParams::fromArray([]);

        expect($params->credentials)->toBeNull()
            ->and($params->toArray())->toBe([]);
    });

    it('serializes credentials', function () {
        $params = new SessionSetCredentialsParams(
            credentials: new AuthInfo(
                host: 'https://github.com',
                type: AuthInfoType::GH_CLI,
                login: 'octocat',
                token: 'gho_test',
            ),
        );

        expect($params->toArray())->toBe([
            'credentials' => [
                'host' => 'https://github.com',
                'type' => 'gh-cli',
                'login' => 'octocat',
                'token' => 'gho_test',
            ],
        ]);
    });
});

describe('SessionSetCredentialsResult', function () {
    it('can be created from array', function () {
        $result = SessionSetCredentialsResult::fromArray(['success' => true]);

        expect($result->success)->toBeTrue()
            ->and($result->toArray())->toBe(['success' => true]);
    });
});
