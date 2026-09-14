<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\JsonSchemaResponseFormat;
use Revolution\Copilot\Types\Rpc\ResponseFormat;

describe('JsonSchemaResponseFormat', function () {
    it('can be created from array', function () {
        $format = JsonSchemaResponseFormat::fromArray([
            'name' => 'my_schema',
            'schema' => ['type' => 'object'],
            'description' => 'A schema',
            'strict' => true,
        ]);

        expect($format->name)->toBe('my_schema')
            ->and($format->schema)->toBe(['type' => 'object'])
            ->and($format->description)->toBe('A schema')
            ->and($format->strict)->toBeTrue();
    });

    it('converts to array correctly', function () {
        $format = new JsonSchemaResponseFormat(name: 'my_schema', schema: ['type' => 'object']);

        expect($format->toArray())->toBe([
            'name' => 'my_schema',
            'schema' => ['type' => 'object'],
        ]);
    });

    it('omits optional fields when null', function () {
        $format = new JsonSchemaResponseFormat(name: 'n', schema: []);

        expect($format->toArray())->toBe(['name' => 'n', 'schema' => []]);
    });
});

describe('ResponseFormat', function () {
    it('can be created from array', function () {
        $format = ResponseFormat::fromArray([
            'jsonSchema' => ['name' => 'my_schema', 'schema' => ['type' => 'object']],
        ]);

        expect($format->jsonSchema)->toBeInstanceOf(JsonSchemaResponseFormat::class)
            ->and($format->type)->toBe('json_schema');
    });

    it('converts to array correctly', function () {
        $format = new ResponseFormat(
            jsonSchema: new JsonSchemaResponseFormat(name: 'my_schema', schema: ['type' => 'object']),
        );

        expect($format->toArray())->toBe([
            'jsonSchema' => ['name' => 'my_schema', 'schema' => ['type' => 'object']],
            'type' => 'json_schema',
        ]);
    });
});
