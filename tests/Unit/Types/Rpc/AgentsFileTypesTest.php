<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentFileLocation;
use Revolution\Copilot\Enums\AgentsCreateFileResultKind;
use Revolution\Copilot\Enums\AgentToolCategoryId;
use Revolution\Copilot\Enums\SpawnAgentNameValidationReason;
use Revolution\Copilot\Enums\SpawnCwdValidationReason;
use Revolution\Copilot\Types\Rpc\AgentsCreateFileRequest;
use Revolution\Copilot\Types\Rpc\AgentsCreateFileResult;
use Revolution\Copilot\Types\Rpc\AgentsGenerateFileContentRequest;
use Revolution\Copilot\Types\Rpc\AgentsGenerateFileContentResult;
use Revolution\Copilot\Types\Rpc\AgentsListToolCategoriesResult;
use Revolution\Copilot\Types\Rpc\AgentsValidateNameRequest;
use Revolution\Copilot\Types\Rpc\AgentsValidateNameResult;
use Revolution\Copilot\Types\Rpc\AgentsValidateSpawnAgentNameRequest;
use Revolution\Copilot\Types\Rpc\AgentsValidateSpawnAgentNameResult;
use Revolution\Copilot\Types\Rpc\AgentsValidateSpawnCwdRequest;
use Revolution\Copilot\Types\Rpc\AgentsValidateSpawnCwdResult;
use Revolution\Copilot\Types\Rpc\AgentToolCategory;
use Revolution\Copilot\Types\Rpc\SpawnValidatableAgent;

describe('custom agent file RPC types', function () {
    it('round-trips spawn validation requests including nested agents', function () {
        $data = [
            'name' => 'review',
            'customAgents' => [['name' => 'review', 'id' => 'team/review'], ['name' => 'other']],
            'builtinNames' => ['explore'],
        ];
        $request = AgentsValidateSpawnAgentNameRequest::fromArray($data);

        expect($request->customAgents[0])->toBeInstanceOf(SpawnValidatableAgent::class)
            ->and($request->toArray())->toBe($data)
            ->and((new AgentsValidateSpawnAgentNameRequest('explore', [], []))->toArray())->toBe([
                'name' => 'explore', 'customAgents' => [], 'builtinNames' => [],
            ])
            ->and(AgentsValidateSpawnCwdRequest::fromArray(['cwd' => '/workspace'])->toArray())
            ->toBe(['cwd' => '/workspace']);
    });

    it('round-trips spawn validation results including unknown reasons', function () {
        $nameResult = AgentsValidateSpawnAgentNameResult::fromArray([
            'ok' => false, 'reason' => 'unknown-agent', 'message' => 'Unknown agent',
        ]);
        $cwdResult = AgentsValidateSpawnCwdResult::fromArray([
            'ok' => false, 'reason' => 'cwd-not-directory', 'message' => 'Not a directory',
        ]);

        expect($nameResult->reason)->toBe(SpawnAgentNameValidationReason::UnknownAgent)
            ->and($nameResult->toArray())->toBe(['ok' => false, 'reason' => 'unknown-agent', 'message' => 'Unknown agent'])
            ->and($cwdResult->reason)->toBe(SpawnCwdValidationReason::CwdNotDirectory)
            ->and($cwdResult->toArray())->toBe(['ok' => false, 'reason' => 'cwd-not-directory', 'message' => 'Not a directory'])
            ->and(AgentsValidateSpawnCwdResult::fromArray(['ok' => false, 'reason' => 'cwd-not-found'])->reason)
            ->toBe(SpawnCwdValidationReason::CwdNotFound)
            ->and(AgentsValidateSpawnAgentNameResult::fromArray(['ok' => true])->toArray())->toBe(['ok' => true])
            ->and(AgentsValidateSpawnCwdResult::fromArray(['ok' => true])->toArray())->toBe(['ok' => true])
            ->and(AgentsValidateSpawnAgentNameResult::fromArray(['ok' => false, 'reason' => 'future-name'])->toArray())
            ->toBe(['ok' => false, 'reason' => 'future-name'])
            ->and(AgentsValidateSpawnCwdResult::fromArray(['ok' => false, 'reason' => 'future-cwd'])->toArray())
            ->toBe(['ok' => false, 'reason' => 'future-cwd']);
    });

    it('round-trips name validation and content generation values', function () {
        $nameRequest = AgentsValidateNameRequest::fromArray(['name' => 'Code Review']);
        $nameResult = AgentsValidateNameResult::fromArray(['valid' => false]);
        $contentRequest = AgentsGenerateFileContentRequest::fromArray([
            'name' => 'Code Review',
            'description' => 'Reviews changes',
            'tools' => [],
            'instructions' => 'Review the code.',
        ]);
        $contentResult = AgentsGenerateFileContentResult::fromArray(['content' => '# Code Review']);

        expect($nameRequest->toArray())->toBe(['name' => 'Code Review'])
            ->and($nameResult->valid)->toBeFalse()
            ->and($nameResult->toArray())->toBe(['valid' => false])
            ->and($contentRequest->tools)->toBe([])
            ->and($contentRequest->toArray())->toBe([
                'name' => 'Code Review',
                'description' => 'Reviews changes',
                'tools' => [],
                'instructions' => 'Review the code.',
            ])
            ->and(AgentsGenerateFileContentRequest::fromArray([
                'name' => 'Code Review',
                'description' => 'Reviews changes',
                'instructions' => 'Review the code.',
            ])->toArray())->toBe([
                'name' => 'Code Review',
                'description' => 'Reviews changes',
                'instructions' => 'Review the code.',
            ])
            ->and($contentResult->toArray())->toBe(['content' => '# Code Review']);
    });

    it('decodes and serializes the available tool categories', function () {
        $categories = AgentsListToolCategoriesResult::fromArray([
            'categories' => [[
                'id' => 'ask-user',
                'label' => 'Ask the user',
                'tools' => ['ask_user'],
            ]],
        ]);

        expect($categories->categories[0])->toBeInstanceOf(AgentToolCategory::class)
            ->and($categories->categories[0]->id)->toBe(AgentToolCategoryId::AskUser)
            ->and($categories->toArray())->toBe([
                'categories' => [[
                    'id' => 'ask-user',
                    'label' => 'Ask the user',
                    'tools' => ['ask_user'],
                ]],
            ]);
    });

    it('round-trips create-file requests and both result variants', function () {
        $request = AgentsCreateFileRequest::fromArray([
            'name' => 'Code Review',
            'description' => 'Reviews changes',
            'instructions' => 'Review the code.',
            'enabledCategories' => ['read', 'edit'],
            'location' => 'user',
            'projectPath' => '/workspace',
            'userConfigDir' => '/home/user/.copilot',
        ]);
        $success = AgentsCreateFileResult::fromArray([
            'kind' => 'success',
            'filePath' => '/workspace/.github/agents/code-review.md',
        ]);
        $error = AgentsCreateFileResult::fromArray([
            'kind' => 'error',
            'message' => 'The file could not be created.',
        ]);
        $requestWithoutCategories = new AgentsCreateFileRequest(
            name: 'Code Review',
            description: 'Reviews changes',
            instructions: 'Review the code.',
            location: AgentFileLocation::Project,
            projectPath: '/workspace',
            userConfigDir: '/home/user/.copilot',
        );

        expect($request->enabledCategories)->toBe([AgentToolCategoryId::Read, AgentToolCategoryId::Edit])
            ->and($request->location)->toBe(AgentFileLocation::User)
            ->and($request->toArray())->toBe([
                'name' => 'Code Review',
                'description' => 'Reviews changes',
                'instructions' => 'Review the code.',
                'enabledCategories' => ['read', 'edit'],
                'location' => 'user',
                'projectPath' => '/workspace',
                'userConfigDir' => '/home/user/.copilot',
            ])
            ->and($requestWithoutCategories->toArray())->not->toHaveKey('enabledCategories')
            ->and($success->kind)->toBe(AgentsCreateFileResultKind::Success)
            ->and($success->toArray())->toBe([
                'kind' => 'success',
                'filePath' => '/workspace/.github/agents/code-review.md',
            ])
            ->and($error->kind)->toBe(AgentsCreateFileResultKind::Error)
            ->and($error->toArray())->toBe([
                'kind' => 'error',
                'message' => 'The file could not be created.',
            ]);
    });

});
