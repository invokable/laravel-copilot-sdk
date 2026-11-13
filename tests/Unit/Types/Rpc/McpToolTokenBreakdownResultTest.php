<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\McpServerToolTokenBreakdown;
use Revolution\Copilot\Types\Rpc\McpToolTokenBreakdownResult;
use Revolution\Copilot\Types\Rpc\McpToolTokenCount;

describe('McpToolTokenBreakdownResult', function () {
    it('maps nested server and tool token costs and serializes them', function () {
        $data = [
            'servers' => [
                [
                    'serverName' => 'github',
                    'totalTokens' => 42,
                    'tools' => [
                        [
                            'name' => 'github_search',
                            'mcpToolName' => 'search',
                            'tokens' => 42,
                            'deferred' => false,
                        ],
                        [
                            'name' => 'github_issues',
                            'mcpToolName' => 'issues',
                            'tokens' => 0,
                            'deferred' => true,
                        ],
                    ],
                ],
            ],
        ];

        $result = McpToolTokenBreakdownResult::fromArray($data);

        expect($result->servers[0])->toBeInstanceOf(McpServerToolTokenBreakdown::class)
            ->and($result->servers[0]->serverName)->toBe('github')
            ->and($result->servers[0]->totalTokens)->toBe(42)
            ->and($result->servers[0]->tools[0])->toBeInstanceOf(McpToolTokenCount::class)
            ->and($result->servers[0]->tools[0]->name)->toBe('github_search')
            ->and($result->servers[0]->tools[0]->mcpToolName)->toBe('search')
            ->and($result->servers[0]->tools[0]->tokens)->toBe(42)
            ->and($result->servers[0]->tools[0]->deferred)->toBeFalse()
            ->and($result->servers[0]->tools[1]->tokens)->toBe(0)
            ->and($result->servers[0]->tools[1]->deferred)->toBeTrue()
            ->and($result->toArray())->toBe($data);
    });

    it('returns an empty server list when no servers are reported', function () {
        $result = McpToolTokenBreakdownResult::fromArray([]);

        expect($result->servers)->toBe([])
            ->and($result->toArray())->toBe(['servers' => []]);
    });
});
