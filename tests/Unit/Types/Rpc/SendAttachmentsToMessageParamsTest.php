<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SendAttachmentsToMessageParams;

describe('SendAttachmentsToMessageParams', function () {
    it('can be created from array with all fields', function () {
        $params = SendAttachmentsToMessageParams::fromArray([
            'attachments' => [['type' => 'file', 'path' => '/tmp/test.php', 'displayName' => 'test.php']],
            'instanceId' => 'canvas-instance-123',
        ]);

        expect($params->attachments)->toHaveCount(1)
            ->and($params->attachments[0]['type'])->toBe('file')
            ->and($params->instanceId)->toBe('canvas-instance-123');
    });

    it('handles default values', function () {
        $params = SendAttachmentsToMessageParams::fromArray([]);

        expect($params->attachments)->toBe([])
            ->and($params->instanceId)->toBeNull();
    });

    it('converts to array', function () {
        $params = SendAttachmentsToMessageParams::fromArray([
            'attachments' => [['type' => 'file', 'path' => '/tmp/test.php', 'displayName' => 'test.php']],
        ]);

        expect($params->toArray())->toHaveKey('attachments');
    });

    it('omits null instanceId from array output', function () {
        $params = SendAttachmentsToMessageParams::fromArray([
            'attachments' => [['type' => 'file', 'path' => '/tmp/test.php', 'displayName' => 'test.php']],
        ]);

        expect($params->toArray())->not->toHaveKey('instanceId');
    });

    it('includes instanceId when set', function () {
        $params = new SendAttachmentsToMessageParams(
            attachments: [],
            instanceId: 'canvas-123',
        );

        expect($params->toArray())->toHaveKey('instanceId', 'canvas-123');
    });
});
