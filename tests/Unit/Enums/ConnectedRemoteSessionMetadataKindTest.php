<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\ConnectedRemoteSessionMetadataKind;

describe('ConnectedRemoteSessionMetadataKind', function () {
    it('has coding-agent case', function () {
        expect(ConnectedRemoteSessionMetadataKind::CodingAgent->value)->toBe('coding-agent');
    });

    it('has remote-session case', function () {
        expect(ConnectedRemoteSessionMetadataKind::RemoteSession->value)->toBe('remote-session');
    });

    it('can create from string', function () {
        expect(ConnectedRemoteSessionMetadataKind::from('coding-agent'))->toBe(ConnectedRemoteSessionMetadataKind::CodingAgent)
            ->and(ConnectedRemoteSessionMetadataKind::from('remote-session'))->toBe(ConnectedRemoteSessionMetadataKind::RemoteSession);
    });

    it('returns null for unknown value with tryFrom', function () {
        expect(ConnectedRemoteSessionMetadataKind::tryFrom('unknown'))->toBeNull();
    });
});
