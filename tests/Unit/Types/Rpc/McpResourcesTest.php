<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\McpResource;
use Revolution\Copilot\Types\Rpc\McpResourceContent;
use Revolution\Copilot\Types\Rpc\McpResourcesListRequest;
use Revolution\Copilot\Types\Rpc\McpResourcesListResult;
use Revolution\Copilot\Types\Rpc\McpResourcesListTemplatesRequest;
use Revolution\Copilot\Types\Rpc\McpResourcesListTemplatesResult;
use Revolution\Copilot\Types\Rpc\McpResourcesReadRequest;
use Revolution\Copilot\Types\Rpc\McpResourcesReadResult;
use Revolution\Copilot\Types\Rpc\McpResourceTemplate;
use Revolution\Copilot\Types\Rpc\McpRestartServerRequest;

describe('McpResource', function () {
    it('can be created with required fields', function () {
        $resource = new McpResource(uri: 'ui://example', name: 'example');

        expect($resource->uri)->toBe('ui://example')
            ->and($resource->name)->toBe('example')
            ->and($resource->title)->toBeNull();
    });

    it('can be created from array', function () {
        $resource = McpResource::fromArray([
            'uri' => 'file:///foo.txt',
            'name' => 'foo',
            'title' => 'Foo File',
            'mimeType' => 'text/plain',
        ]);

        expect($resource->uri)->toBe('file:///foo.txt')
            ->and($resource->name)->toBe('foo')
            ->and($resource->title)->toBe('Foo File')
            ->and($resource->mimeType)->toBe('text/plain');
    });

    it('converts to array excluding nulls', function () {
        $resource = new McpResource(uri: 'ui://x', name: 'x');

        expect($resource->toArray())->toBe(['uri' => 'ui://x', 'name' => 'x']);
    });
});

describe('McpResourcesListRequest', function () {
    it('can be created with serverName only', function () {
        $request = new McpResourcesListRequest(serverName: 'myServer');

        expect($request->serverName)->toBe('myServer')
            ->and($request->cursor)->toBeNull();
    });

    it('can be created from array with cursor', function () {
        $request = McpResourcesListRequest::fromArray(['serverName' => 'srv', 'cursor' => 'abc123']);

        expect($request->serverName)->toBe('srv')
            ->and($request->cursor)->toBe('abc123');
    });

    it('converts to array excluding null cursor', function () {
        $request = new McpResourcesListRequest(serverName: 'srv');

        expect($request->toArray())->toBe(['serverName' => 'srv']);
    });
});

describe('McpResourcesListResult', function () {
    it('can be created from array', function () {
        $result = McpResourcesListResult::fromArray([
            'resources' => [
                ['uri' => 'ui://a', 'name' => 'a'],
            ],
            'nextCursor' => 'cursor1',
        ]);

        expect($result->resources)->toHaveCount(1)
            ->and($result->resources[0])->toBeInstanceOf(McpResource::class)
            ->and($result->nextCursor)->toBe('cursor1');
    });

    it('handles empty resources', function () {
        $result = McpResourcesListResult::fromArray(['resources' => []]);

        expect($result->resources)->toBeEmpty()
            ->and($result->nextCursor)->toBeNull();
    });
});

describe('McpResourcesReadRequest', function () {
    it('can be created', function () {
        $request = new McpResourcesReadRequest(serverName: 'srv', uri: 'ui://res');

        expect($request->serverName)->toBe('srv')
            ->and($request->uri)->toBe('ui://res');
    });

    it('converts to array', function () {
        $request = McpResourcesReadRequest::fromArray(['serverName' => 'srv', 'uri' => 'ui://res']);

        expect($request->toArray())->toBe(['serverName' => 'srv', 'uri' => 'ui://res']);
    });
});

describe('McpResourcesReadResult', function () {
    it('can be created from array', function () {
        $result = McpResourcesReadResult::fromArray([
            'contents' => [
                ['uri' => 'ui://a', 'text' => 'hello'],
            ],
        ]);

        expect($result->contents)->toHaveCount(1)
            ->and($result->contents[0])->toBeInstanceOf(McpResourceContent::class)
            ->and($result->contents[0]->text)->toBe('hello');
    });
});

describe('McpResourceContent', function () {
    it('can be created with uri only', function () {
        $content = new McpResourceContent(uri: 'ui://x');

        expect($content->uri)->toBe('ui://x')
            ->and($content->text)->toBeNull();
    });

    it('converts to array excluding nulls', function () {
        $content = new McpResourceContent(uri: 'ui://x', text: 'hello');

        expect($content->toArray())->toBe(['uri' => 'ui://x', 'text' => 'hello']);
    });
});

describe('McpResourceTemplate', function () {
    it('can be created from array', function () {
        $template = McpResourceTemplate::fromArray([
            'uriTemplate' => 'file://{path}',
            'name' => 'file',
            'description' => 'A file resource',
        ]);

        expect($template->uriTemplate)->toBe('file://{path}')
            ->and($template->name)->toBe('file')
            ->and($template->description)->toBe('A file resource');
    });

    it('converts to array excluding nulls', function () {
        $template = new McpResourceTemplate(uriTemplate: 'ui://{id}', name: 'item');

        expect($template->toArray())->toBe(['uriTemplate' => 'ui://{id}', 'name' => 'item']);
    });
});

describe('McpResourcesListTemplatesRequest', function () {
    it('can be created from array', function () {
        $request = McpResourcesListTemplatesRequest::fromArray(['serverName' => 'srv']);

        expect($request->serverName)->toBe('srv')
            ->and($request->cursor)->toBeNull();
    });
});

describe('McpResourcesListTemplatesResult', function () {
    it('can be created from array', function () {
        $result = McpResourcesListTemplatesResult::fromArray([
            'resourceTemplates' => [
                ['uriTemplate' => 'ui://{id}', 'name' => 'item'],
            ],
        ]);

        expect($result->resourceTemplates)->toHaveCount(1)
            ->and($result->resourceTemplates[0])->toBeInstanceOf(McpResourceTemplate::class);
    });
});

describe('McpRestartServerRequest', function () {
    it('can be created with serverName only', function () {
        $request = new McpRestartServerRequest(serverName: 'srv');

        expect($request->serverName)->toBe('srv')
            ->and($request->config)->toBeNull();
    });

    it('converts to array excluding null config', function () {
        $request = new McpRestartServerRequest(serverName: 'srv');

        expect($request->toArray())->toBe(['serverName' => 'srv']);
    });
});
