<?php

declare(strict_types=1);

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

describe('sandbox proxy CA config-directory types', function () {
    it('round-trips configuration-directory parameters and result types', function () {
        $profileParams = [
            'configDir' => '/home/user/copilot-profile',
            'sandboxConfig' => ['enabled' => true],
        ];
        $status = ['state' => 'installed', 'canInstall' => true, 'detail' => 'ready'];

        $params = [
            SandboxProxyCaCreateForConfigDirParams::fromArray($profileParams),
            SandboxProxyCaGetStatusForConfigDirParams::fromArray($profileParams),
            SandboxProxyCaRotateForConfigDirParams::fromArray($profileParams),
            SandboxProxyCaTrustForConfigDirParams::fromArray($profileParams),
            SandboxProxyCaRemoveForConfigDirParams::fromArray($profileParams),
            SandboxProxyCaRotateIfUntrustedParams::fromArray($profileParams),
        ];
        $results = [
            SandboxProxyCaGetStatusForConfigDirResult::fromArray($status),
            SandboxProxyCaRotateForConfigDirResult::fromArray($status),
            SandboxProxyCaTrustForConfigDirResult::fromArray($status),
            SandboxProxyCaRemoveForConfigDirResult::fromArray($status),
            SandboxProxyCaTrustCertificateResult::fromArray($status),
        ];

        expect(array_map(static fn ($param) => $param->toArray(), $params))
            ->toBe(array_fill(0, 6, $profileParams))
            ->and(SandboxProxyCaCreateForConfigDirResult::fromArray([
                'certificatePath' => '/tmp/proxy-ca.pem',
            ])->toArray())
            ->toBe(['certificatePath' => '/tmp/proxy-ca.pem'])
            ->and(array_map(static fn ($result) => $result->toArray(), $results))
            ->toBe(array_fill(0, 5, $status));
    });

    it('round-trips host context and certificate trust parameters', function () {
        $hostContext = [
            'configDir' => '/home/user/copilot-profile',
            'homeDirectory' => '/home/user',
            'environment' => ['COPILOT_HOME' => '/home/user/copilot-profile'],
        ];
        $certificateRequest = [
            'certificateFile' => '/tmp/proxy-ca.pem',
            'allowHosts' => ['registry.example.com'],
            'configDir' => '/home/user/copilot-profile',
        ];

        expect(SandboxProxyCaDeclineParams::fromArray($hostContext)->toArray())
            ->toBe($hostContext)
            ->and(SandboxProxyCaGetDeclinedParams::fromArray($hostContext)->toArray())
            ->toBe($hostContext)
            ->and(SandboxProxyCaTrustCertificateRequest::fromArray($certificateRequest)->toArray())
            ->toBe($certificateRequest)
            ->and((new SandboxProxyCaTrustCertificateRequest(
                certificateFile: '/tmp/proxy-ca.pem',
                configDir: '/home/user/copilot-profile',
            ))->toArray())
            ->toBe([
                'certificateFile' => '/tmp/proxy-ca.pem',
                'configDir' => '/home/user/copilot-profile',
            ]);
    });
});
