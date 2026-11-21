<?php

declare(strict_types=1);

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerSandboxProxyCa;
use Revolution\Copilot\Types\Rpc\SandboxConfig;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaCreateForConfigDirParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaCreateForConfigDirResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaDeclineParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaGetDeclinedParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaGetStatusForConfigDirParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaGetStatusForConfigDirResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaRemoveForConfigDirParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaRemoveForConfigDirResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaRotateForConfigDirParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaRotateForConfigDirResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaRotateIfUntrustedParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaTrustCertificateRequest;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaTrustCertificateResult;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaTrustForConfigDirParams;
use Revolution\Copilot\Types\Rpc\SandboxProxyCaTrustForConfigDirResult;

describe('PendingServerSandboxProxyCa', function () {
    it('checks proxy CA relevance with array and typed sandbox configuration', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.checkRelevance', ['sandboxConfig' => ['enabled' => true]])
            ->andReturn(true);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.checkRelevance', ['sandboxConfig' => ['enabled' => false]])
            ->andReturn(false);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.checkInstallationSupport', [])
            ->andReturn(true);

        $pending = new PendingServerSandboxProxyCa($client);

        expect($pending->checkRelevance(['enabled' => true]))->toBeTrue()
            ->and($pending->checkRelevance(new SandboxConfig(enabled: false)))->toBeFalse()
            ->and($pending->checkInstallationSupport())->toBeTrue();
    });

    it('sends config-directory operations with their typed parameters and results', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $configDir = '/home/user/copilot-profile';
        $sandboxConfig = ['enabled' => true];
        $hostContext = [
            'configDir' => $configDir,
            'homeDirectory' => '/home/user',
            'environment' => ['COPILOT_HOME' => $configDir],
        ];
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.getStatusForConfigDir', [
                'configDir' => $configDir,
                'sandboxConfig' => $sandboxConfig,
            ])
            ->andReturn(['state' => 'installed', 'canInstall' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.createForConfigDir', [
                'configDir' => $configDir,
                'sandboxConfig' => $sandboxConfig,
            ])
            ->andReturn(['certificatePath' => '/tmp/profile-ca.pem']);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.rotateForConfigDir', ['configDir' => $configDir])
            ->andReturn(['state' => 'rotated', 'canInstall' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.trustForConfigDir', ['configDir' => $configDir])
            ->andReturn(['state' => 'trusted', 'canInstall' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.removeForConfigDir', ['configDir' => $configDir])
            ->andReturn(['state' => 'notInstalled', 'canInstall' => true]);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.getDeclined', $hostContext)
            ->andReturn(true);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.decline', $hostContext)
            ->andReturnNull();
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.rotateIfUntrusted', [
                'configDir' => $configDir,
                'sandboxConfig' => $sandboxConfig,
            ])
            ->andReturn(false);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.trustCertificate', [
                'certificateFile' => '/tmp/proxy-ca.pem',
                'allowHosts' => ['registry.example.com'],
                'configDir' => $configDir,
            ])
            ->andReturn(['state' => 'trusted', 'canInstall' => true]);

        $proxyCa = new PendingServerSandboxProxyCa($client);
        $status = $proxyCa->getStatusForConfigDir(new SandboxProxyCaGetStatusForConfigDirParams(
            configDir: $configDir,
            sandboxConfig: new SandboxConfig(enabled: true),
        ));
        $created = $proxyCa->createForConfigDir(new SandboxProxyCaCreateForConfigDirParams(
            configDir: $configDir,
            sandboxConfig: new SandboxConfig(enabled: true),
        ));
        $rotated = $proxyCa->rotateForConfigDir(new SandboxProxyCaRotateForConfigDirParams(configDir: $configDir));
        $trusted = $proxyCa->trustForConfigDir(new SandboxProxyCaTrustForConfigDirParams(configDir: $configDir));
        $removed = $proxyCa->removeForConfigDir(new SandboxProxyCaRemoveForConfigDirParams(configDir: $configDir));
        $declined = $proxyCa->getDeclined(new SandboxProxyCaGetDeclinedParams(...$hostContext));
        $proxyCa->decline(new SandboxProxyCaDeclineParams(...$hostContext));
        $rotatedIfUntrusted = $proxyCa->rotateIfUntrusted(new SandboxProxyCaRotateIfUntrustedParams(
            configDir: $configDir,
            sandboxConfig: ['enabled' => true],
        ));
        $trustedCertificate = $proxyCa->trustCertificate(new SandboxProxyCaTrustCertificateRequest(
            certificateFile: '/tmp/proxy-ca.pem',
            configDir: $configDir,
            allowHosts: ['registry.example.com'],
        ));

        expect($status)->toBeInstanceOf(SandboxProxyCaGetStatusForConfigDirResult::class)
            ->and($status->state)->toBe('installed')
            ->and($created)->toBeInstanceOf(SandboxProxyCaCreateForConfigDirResult::class)
            ->and($created->certificatePath)->toBe('/tmp/profile-ca.pem')
            ->and($rotated)->toBeInstanceOf(SandboxProxyCaRotateForConfigDirResult::class)
            ->and($rotated->state)->toBe('rotated')
            ->and($trusted)->toBeInstanceOf(SandboxProxyCaTrustForConfigDirResult::class)
            ->and($trusted->state)->toBe('trusted')
            ->and($removed)->toBeInstanceOf(SandboxProxyCaRemoveForConfigDirResult::class)
            ->and($removed->state)->toBe('notInstalled')
            ->and($declined)->toBeTrue()
            ->and($rotatedIfUntrusted)->toBeFalse()
            ->and($trustedCertificate)->toBeInstanceOf(SandboxProxyCaTrustCertificateResult::class)
            ->and($trustedCertificate->state)->toBe('trusted');
    });

    it('converts array parameters before sending config-directory operations', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('sandbox.proxyCa.getStatusForConfigDir', [
                'configDir' => '/home/user/profile',
                'sandboxConfig' => ['enabled' => true],
            ])
            ->andReturn(['state' => 'installed']);

        $result = (new PendingServerSandboxProxyCa($client))->getStatusForConfigDir([
            'configDir' => '/home/user/profile',
            'sandboxConfig' => ['enabled' => true],
        ]);

        expect($result)->toBeInstanceOf(SandboxProxyCaGetStatusForConfigDirResult::class)
            ->and($result->state)->toBe('installed');
    });
});
