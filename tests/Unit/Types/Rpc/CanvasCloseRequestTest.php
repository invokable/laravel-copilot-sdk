<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\CanvasCloseRequest;

describe('CanvasCloseRequest', function () {
    it('can be created from array', function () {
        $request = CanvasCloseRequest::fromArray([
            'instanceId' => 'instance-123',
        ]);

        expect($request->instanceId)->toBe('instance-123');
    });

    it('converts to array correctly', function () {
        $request = new CanvasCloseRequest(instanceId: 'instance-456');

        expect($request->toArray())->toBe([
            'instanceId' => 'instance-456',
        ]);
    });
});
