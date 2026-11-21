<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\AgentFileLocation;
use Revolution\Copilot\Enums\AgentsCreateFileResultKind;
use Revolution\Copilot\Enums\AgentToolCategoryId;
use Revolution\Copilot\Types\Rpc\AgentsCreateFileRequest;
use Revolution\Copilot\Types\Rpc\AgentsCreateFileResult;
use Revolution\Copilot\Types\Rpc\AgentsGenerateFileContentRequest;
use Revolution\Copilot\Types\Rpc\AgentsGenerateFileContentResult;
use Revolution\Copilot\Types\Rpc\AgentsListToolCategoriesResult;
use Revolution\Copilot\Types\Rpc\AgentsValidateNameRequest;
use Revolution\Copilot\Types\Rpc\AgentsValidateNameResult;
use Revolution\Copilot\Types\Rpc\AgentToolCategory;

describe('custom agent file RPC types', function () {
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
