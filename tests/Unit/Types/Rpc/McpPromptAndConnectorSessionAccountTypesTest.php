<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AuthIdentityMetadata;
use Revolution\Copilot\Types\Rpc\ConnectorSessionAccount;
use Revolution\Copilot\Types\Rpc\McpPrompt;
use Revolution\Copilot\Types\Rpc\McpPromptArgument;
use Revolution\Copilot\Types\Rpc\McpPromptMessage;

it('round trips nested MCP prompt and connector account data', function () {
    $prompt = McpPrompt::fromArray([
        'name' => 'summarize',
        'arguments' => [['name' => 'document', 'required' => true]],
    ]);
    $account = ConnectorSessionAccount::fromArray([
        'accountId' => 'opaque-account',
        'authInfo' => ['host' => 'github.com', 'login' => 'octocat'],
    ]);
    $message = McpPromptMessage::fromArray([
        'role' => 'user',
        'content' => ['type' => 'text', 'text' => 'Hello'],
    ]);

    expect($prompt->arguments[0])->toBeInstanceOf(McpPromptArgument::class)
        ->and($prompt->toArray()['arguments'][0]['required'])->toBeTrue()
        ->and($account->authInfo)->toBeInstanceOf(AuthIdentityMetadata::class)
        ->and($account->toArray()['authInfo']['login'])->toBe('octocat')
        ->and($message->toArray()['content']['text'])->toBe('Hello');
});
