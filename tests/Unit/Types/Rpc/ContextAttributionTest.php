<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Types\Rpc\ContextHeaviestMessage;
use Revolution\Copilot\Types\Rpc\MetadataContextAttributionResult;
use Revolution\Copilot\Types\Rpc\MetadataContextHeaviestMessagesRequest;
use Revolution\Copilot\Types\Rpc\MetadataContextHeaviestMessagesResult;
use Revolution\Copilot\Types\Rpc\SessionContextAttribution;
use Revolution\Copilot\Types\Rpc\SessionContextAttributionEntry;

describe('SessionContextAttributionEntry', function () {
    it('implements Arrayable', function () {
        expect(new SessionContextAttributionEntry('tool:bash', 'tool', 'bash', 500))
            ->toBeInstanceOf(Arrayable::class);
    });

    it('can be created from array with required fields', function () {
        $entry = SessionContextAttributionEntry::fromArray([
            'id' => 'tool:bash',
            'kind' => 'tool',
            'label' => 'bash',
            'tokens' => 500,
        ]);

        expect($entry->id)->toBe('tool:bash')
            ->and($entry->kind)->toBe('tool')
            ->and($entry->label)->toBe('bash')
            ->and($entry->tokens)->toBe(500)
            ->and($entry->parentId)->toBeNull()
            ->and($entry->attributes)->toBeNull();
    });

    it('can be created from array with all fields', function () {
        $entry = SessionContextAttributionEntry::fromArray([
            'id' => 'skill:tmux',
            'kind' => 'skill',
            'label' => 'skill: tmux',
            'tokens' => 200,
            'parentId' => 'plugin:main',
            'attributes' => ['evictable' => 'true'],
        ]);

        expect($entry->parentId)->toBe('plugin:main')
            ->and($entry->attributes)->toBe(['evictable' => 'true']);
    });

    it('omits null fields from toArray', function () {
        $entry = new SessionContextAttributionEntry('tool:bash', 'tool', 'bash', 500);
        $array = $entry->toArray();

        expect($array)->toHaveKey('id')->toHaveKey('kind')->toHaveKey('label')->toHaveKey('tokens')
            ->and($array)->not->toHaveKey('parentId')
            ->and($array)->not->toHaveKey('attributes');
    });

    it('includes optional fields when set', function () {
        $entry = new SessionContextAttributionEntry(
            'skill:tmux', 'skill', 'skill: tmux', 200,
            parentId: 'plugin:main',
            attributes: ['messageCount' => '5'],
        );

        expect($entry->toArray())->toHaveKey('parentId', 'plugin:main')
            ->and($entry->toArray())->toHaveKey('attributes', ['messageCount' => '5']);
    });
});

describe('SessionContextAttribution', function () {
    it('implements Arrayable', function () {
        expect(new SessionContextAttribution(1000, [], ['count' => 0]))
            ->toBeInstanceOf(Arrayable::class);
    });

    it('can be created from array', function () {
        $attribution = SessionContextAttribution::fromArray([
            'totalTokens' => 1000,
            'entries' => [
                ['id' => 'tool:bash', 'kind' => 'tool', 'label' => 'bash', 'tokens' => 500],
                ['id' => 'system', 'kind' => 'system', 'label' => 'system', 'tokens' => 500],
            ],
            'compactions' => ['count' => 2],
        ]);

        expect($attribution->totalTokens)->toBe(1000)
            ->and($attribution->entries)->toHaveCount(2)
            ->and($attribution->entries[0])->toBeInstanceOf(SessionContextAttributionEntry::class)
            ->and($attribution->entries[0]->id)->toBe('tool:bash')
            ->and($attribution->compactions)->toBe(['count' => 2]);
    });

    it('defaults to empty entries and zero compactions', function () {
        $attribution = SessionContextAttribution::fromArray(['totalTokens' => 500]);

        expect($attribution->entries)->toBe([])
            ->and($attribution->compactions)->toBe(['count' => 0]);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = [
            'totalTokens' => 800,
            'entries' => [
                ['id' => 'tool:bash', 'kind' => 'tool', 'label' => 'bash', 'tokens' => 800],
            ],
            'compactions' => ['count' => 1],
        ];

        $attribution = SessionContextAttribution::fromArray($data);

        expect($attribution->toArray())->toBe($data);
    });
});

describe('MetadataContextAttributionResult', function () {
    it('implements Arrayable', function () {
        expect(new MetadataContextAttributionResult)
            ->toBeInstanceOf(Arrayable::class);
    });

    it('returns null attribution when uninitialized', function () {
        $result = MetadataContextAttributionResult::fromArray([]);

        expect($result->contextAttribution)->toBeNull();
    });

    it('parses nested attribution', function () {
        $result = MetadataContextAttributionResult::fromArray([
            'contextAttribution' => [
                'totalTokens' => 100,
                'entries' => [],
                'compactions' => ['count' => 0],
            ],
        ]);

        expect($result->contextAttribution)->toBeInstanceOf(SessionContextAttribution::class)
            ->and($result->contextAttribution->totalTokens)->toBe(100);
    });

    it('omits null attribution from toArray', function () {
        $result = new MetadataContextAttributionResult;

        expect($result->toArray())->not->toHaveKey('contextAttribution');
    });
});

describe('MetadataContextHeaviestMessagesRequest', function () {
    it('implements Arrayable', function () {
        expect(new MetadataContextHeaviestMessagesRequest)
            ->toBeInstanceOf(Arrayable::class);
    });

    it('defaults limit to null', function () {
        $req = new MetadataContextHeaviestMessagesRequest;

        expect($req->limit)->toBeNull()
            ->and($req->toArray())->toBe([]);
    });

    it('can set limit', function () {
        $req = new MetadataContextHeaviestMessagesRequest(limit: 5);

        expect($req->limit)->toBe(5)
            ->and($req->toArray())->toBe(['limit' => 5]);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = ['limit' => 10];
        $req = MetadataContextHeaviestMessagesRequest::fromArray($data);

        expect($req->toArray())->toBe($data);
    });
});

describe('MetadataContextHeaviestMessagesResult', function () {
    it('implements Arrayable', function () {
        expect(new MetadataContextHeaviestMessagesResult(0, []))
            ->toBeInstanceOf(Arrayable::class);
    });

    it('can be created from array', function () {
        $result = MetadataContextHeaviestMessagesResult::fromArray([
            'totalTokens' => 5000,
            'messages' => [
                ['id' => 'msg-1', 'label' => 'tool: bash', 'role' => 'tool', 'tokens' => 3000],
                ['id' => 'msg-2', 'label' => 'assistant', 'role' => 'assistant', 'tokens' => 2000],
            ],
        ]);

        expect($result->totalTokens)->toBe(5000)
            ->and($result->messages)->toHaveCount(2)
            ->and($result->messages[0])->toBeInstanceOf(ContextHeaviestMessage::class)
            ->and($result->messages[0]->tokens)->toBe(3000);
    });

    it('defaults to empty messages', function () {
        $result = MetadataContextHeaviestMessagesResult::fromArray(['totalTokens' => 0]);

        expect($result->messages)->toBe([]);
    });

    it('roundtrips through fromArray and toArray', function () {
        $data = [
            'totalTokens' => 1500,
            'messages' => [
                ['id' => 'msg-1', 'label' => 'bash', 'role' => 'tool', 'tokens' => 1500],
            ],
        ];

        $result = MetadataContextHeaviestMessagesResult::fromArray($data);

        expect($result->toArray())->toBe($data);
    });
});
