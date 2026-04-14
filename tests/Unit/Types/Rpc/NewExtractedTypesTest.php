<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\Enums\McpTransportType;
use Revolution\Copilot\Types\Rpc\EmbeddedBlobResourceContents;
use Revolution\Copilot\Types\Rpc\EmbeddedTextResourceContents;
use Revolution\Copilot\Types\Rpc\UIElicitationArrayAnyOfField;
use Revolution\Copilot\Types\Rpc\UIElicitationArrayEnumField;
use Revolution\Copilot\Types\Rpc\UIElicitationResponse;
use Revolution\Copilot\Types\Rpc\UIElicitationStringEnumField;
use Revolution\Copilot\Types\Rpc\UIElicitationStringOneOfField;

describe('McpTransportType', function () {
    it('has all expected transport types', function () {
        expect(McpTransportType::STDIO->value)->toBe('stdio')
            ->and(McpTransportType::HTTP->value)->toBe('http')
            ->and(McpTransportType::SSE->value)->toBe('sse')
            ->and(McpTransportType::MEMORY->value)->toBe('memory');
    });

    it('can be created from string', function () {
        expect(McpTransportType::from('stdio'))->toBe(McpTransportType::STDIO)
            ->and(McpTransportType::from('http'))->toBe(McpTransportType::HTTP)
            ->and(McpTransportType::from('sse'))->toBe(McpTransportType::SSE)
            ->and(McpTransportType::from('memory'))->toBe(McpTransportType::MEMORY);
    });

    it('returns null for unknown transport types', function () {
        expect(McpTransportType::tryFrom('unknown'))->toBeNull();
    });
});

describe('EmbeddedTextResourceContents', function () {
    it('can be created from array', function () {
        $resource = EmbeddedTextResourceContents::fromArray([
            'uri' => 'file:///test.txt',
            'text' => 'Hello world',
            'mimeType' => 'text/plain',
        ]);

        expect($resource->uri)->toBe('file:///test.txt')
            ->and($resource->text)->toBe('Hello world')
            ->and($resource->mimeType)->toBe('text/plain');
    });

    it('can be created without optional mimeType', function () {
        $resource = EmbeddedTextResourceContents::fromArray([
            'uri' => 'file:///test.txt',
            'text' => 'Hello',
        ]);

        expect($resource->mimeType)->toBeNull();
    });

    it('converts to array', function () {
        $resource = new EmbeddedTextResourceContents(
            uri: 'file:///test.txt',
            text: 'Hello',
            mimeType: 'text/plain',
        );

        expect($resource->toArray())->toBe([
            'uri' => 'file:///test.txt',
            'text' => 'Hello',
            'mimeType' => 'text/plain',
        ]);
    });

    it('excludes null mimeType from toArray', function () {
        $resource = new EmbeddedTextResourceContents(uri: 'x', text: 'y');

        expect($resource->toArray())->not->toHaveKey('mimeType');
    });

    it('implements Arrayable interface', function () {
        expect(new EmbeddedTextResourceContents(uri: 'x', text: 'y'))->toBeInstanceOf(Arrayable::class);
    });
});

describe('EmbeddedBlobResourceContents', function () {
    it('can be created from array', function () {
        $resource = EmbeddedBlobResourceContents::fromArray([
            'uri' => 'file:///test.png',
            'blob' => base64_encode('binary-data'),
            'mimeType' => 'image/png',
        ]);

        expect($resource->uri)->toBe('file:///test.png')
            ->and($resource->blob)->toBe(base64_encode('binary-data'))
            ->and($resource->mimeType)->toBe('image/png');
    });

    it('can be created without optional mimeType', function () {
        $resource = EmbeddedBlobResourceContents::fromArray([
            'uri' => 'file:///test.bin',
            'blob' => 'abc123',
        ]);

        expect($resource->mimeType)->toBeNull();
    });

    it('converts to array', function () {
        $resource = new EmbeddedBlobResourceContents(
            uri: 'file:///test.png',
            blob: 'abc123',
            mimeType: 'image/png',
        );

        expect($resource->toArray())->toBe([
            'uri' => 'file:///test.png',
            'blob' => 'abc123',
            'mimeType' => 'image/png',
        ]);
    });

    it('implements Arrayable interface', function () {
        expect(new EmbeddedBlobResourceContents(uri: 'x', blob: 'y'))->toBeInstanceOf(Arrayable::class);
    });
});

describe('UIElicitationResponse', function () {
    it('can be created with accept action and content', function () {
        $response = UIElicitationResponse::fromArray([
            'action' => 'accept',
            'content' => ['name' => 'John', 'age' => 30],
        ]);

        expect($response->action)->toBe(ElicitationAction::ACCEPT)
            ->and($response->content)->toBe(['name' => 'John', 'age' => 30]);
    });

    it('can be created with decline action', function () {
        $response = UIElicitationResponse::fromArray([
            'action' => 'decline',
        ]);

        expect($response->action)->toBe(ElicitationAction::DECLINE)
            ->and($response->content)->toBeNull();
    });

    it('can be created with cancel action', function () {
        $response = UIElicitationResponse::fromArray([
            'action' => 'cancel',
        ]);

        expect($response->action)->toBe(ElicitationAction::CANCEL);
    });

    it('handles unknown action as string fallback', function () {
        $response = UIElicitationResponse::fromArray([
            'action' => 'unknown-future-action',
        ]);

        expect($response->action)->toBe('unknown-future-action');
    });

    it('converts to array', function () {
        $response = new UIElicitationResponse(
            action: ElicitationAction::ACCEPT,
            content: ['choice' => 'A'],
        );

        expect($response->toArray())->toBe([
            'action' => 'accept',
            'content' => ['choice' => 'A'],
        ]);
    });

    it('excludes null content from toArray', function () {
        $response = new UIElicitationResponse(action: ElicitationAction::DECLINE);

        expect($response->toArray())->not->toHaveKey('content');
    });

    it('implements Arrayable interface', function () {
        expect(new UIElicitationResponse(action: ElicitationAction::ACCEPT))->toBeInstanceOf(Arrayable::class);
    });
});

describe('UIElicitationStringEnumField', function () {
    it('can be created from array', function () {
        $field = UIElicitationStringEnumField::fromArray([
            'type' => 'string',
            'enum' => ['opt1', 'opt2'],
            'description' => 'Select an option',
            'enumNames' => ['Option 1', 'Option 2'],
            'default' => 'opt1',
        ]);

        expect($field->enum)->toBe(['opt1', 'opt2'])
            ->and($field->description)->toBe('Select an option')
            ->and($field->enumNames)->toBe(['Option 1', 'Option 2'])
            ->and($field->default)->toBe('opt1');
    });

    it('converts to array with type field', function () {
        $field = new UIElicitationStringEnumField(
            enum: ['a', 'b'],
            description: 'Pick one',
        );

        $array = $field->toArray();

        expect($array['type'])->toBe('string')
            ->and($array['enum'])->toBe(['a', 'b'])
            ->and($array['description'])->toBe('Pick one');
    });

    it('implements Arrayable interface', function () {
        expect(new UIElicitationStringEnumField(enum: ['a']))->toBeInstanceOf(Arrayable::class);
    });
});

describe('UIElicitationStringOneOfField', function () {
    it('can be created from array', function () {
        $field = UIElicitationStringOneOfField::fromArray([
            'type' => 'string',
            'oneOf' => [
                ['const' => 'a'],
                ['const' => 'b'],
            ],
            'default' => 'a',
        ]);

        expect($field->oneOf)->toHaveCount(2)
            ->and($field->default)->toBe('a');
    });

    it('converts to array with type field', function () {
        $field = new UIElicitationStringOneOfField(
            oneOf: [['const' => 'x']],
        );

        expect($field->toArray()['type'])->toBe('string')
            ->and($field->toArray()['oneOf'])->toBe([['const' => 'x']]);
    });

    it('implements Arrayable interface', function () {
        expect(new UIElicitationStringOneOfField(oneOf: []))->toBeInstanceOf(Arrayable::class);
    });
});

describe('UIElicitationArrayEnumField', function () {
    it('can be created from array', function () {
        $field = UIElicitationArrayEnumField::fromArray([
            'type' => 'array',
            'items' => ['type' => 'string', 'enum' => ['opt1', 'opt2']],
            'minItems' => 1,
            'maxItems' => 3,
        ]);

        expect($field->items['enum'])->toBe(['opt1', 'opt2'])
            ->and($field->minItems)->toBe(1)
            ->and($field->maxItems)->toBe(3);
    });

    it('converts to array with type field', function () {
        $field = new UIElicitationArrayEnumField(
            items: ['type' => 'string', 'enum' => ['a']],
        );

        expect($field->toArray()['type'])->toBe('array');
    });

    it('implements Arrayable interface', function () {
        expect(new UIElicitationArrayEnumField(items: []))->toBeInstanceOf(Arrayable::class);
    });
});

describe('UIElicitationArrayAnyOfField', function () {
    it('can be created from array', function () {
        $field = UIElicitationArrayAnyOfField::fromArray([
            'type' => 'array',
            'items' => ['anyOf' => [['const' => 'a'], ['const' => 'b']]],
            'default' => ['a'],
        ]);

        expect($field->items['anyOf'])->toHaveCount(2)
            ->and($field->default)->toBe(['a']);
    });

    it('converts to array with type field', function () {
        $field = new UIElicitationArrayAnyOfField(
            items: ['anyOf' => [['const' => 'x']]],
        );

        expect($field->toArray()['type'])->toBe('array');
    });

    it('implements Arrayable interface', function () {
        expect(new UIElicitationArrayAnyOfField(items: []))->toBeInstanceOf(Arrayable::class);
    });
});
