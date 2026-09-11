<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\MetadataUpdateClientMetadataRequest;

describe('MetadataUpdateClientMetadataRequest', function () {
    it('can be created with all fields', function () {
        $request = new MetadataUpdateClientMetadataRequest(
            clear: true,
            remove: ['key1'],
            set: ['key2' => 'value2'],
        );

        expect($request->clear)->toBeTrue()
            ->and($request->remove)->toBe(['key1'])
            ->and($request->set)->toBe(['key2' => 'value2']);
    });

    it('handles default values', function () {
        $request = new MetadataUpdateClientMetadataRequest;

        expect($request->clear)->toBeNull()
            ->and($request->remove)->toBeNull()
            ->and($request->set)->toBeNull();
    });

    it('can be created from array', function () {
        $request = MetadataUpdateClientMetadataRequest::fromArray([
            'set' => ['key' => 'value'],
        ]);

        expect($request->set)->toBe(['key' => 'value'])
            ->and($request->clear)->toBeNull();
    });

    it('serializes to array omitting null values', function () {
        $request = new MetadataUpdateClientMetadataRequest;

        expect($request->toArray())->toBe([]);
    });

    it('roundtrips through fromArray/toArray', function () {
        $data = [
            'clear' => false,
            'remove' => ['a', 'b'],
            'set' => ['c' => 'd'],
        ];

        expect(MetadataUpdateClientMetadataRequest::fromArray($data)->toArray())->toBe($data);
    });
});
