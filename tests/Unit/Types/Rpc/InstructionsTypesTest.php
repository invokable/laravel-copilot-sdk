<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\InstructionsSourcesLocation;
use Revolution\Copilot\Enums\InstructionsSourcesType;
use Revolution\Copilot\Enums\SessionFSErrorCode;
use Revolution\Copilot\Types\Rpc\InstructionsGetSourcesResult;
use Revolution\Copilot\Types\Rpc\InstructionsSources;
use Revolution\Copilot\Types\Rpc\SessionFSError;

describe('SessionFSError', function () {
    it('can be created from array with all fields', function () {
        $error = SessionFSError::fromArray([
            'code' => 'ENOENT',
            'message' => 'File not found',
        ]);

        expect($error->code)->toBe(SessionFSErrorCode::ENOENT)
            ->and($error->message)->toBe('File not found');
    });

    it('can be created without optional message', function () {
        $error = SessionFSError::fromArray(['code' => 'UNKNOWN']);

        expect($error->code)->toBe(SessionFSErrorCode::UNKNOWN)
            ->and($error->message)->toBeNull();
    });

    it('converts to array', function () {
        $error = SessionFSError::fromArray([
            'code' => 'ENOENT',
            'message' => 'File not found',
        ]);

        expect($error->toArray())->toBe([
            'code' => 'ENOENT',
            'message' => 'File not found',
        ]);
    });

    it('filters null message in toArray', function () {
        $error = new SessionFSError(code: SessionFSErrorCode::UNKNOWN);

        expect($error->toArray())->not->toHaveKey('message');
    });
});

describe('InstructionsSources', function () {
    it('can be created from array with all fields', function () {
        $source = InstructionsSources::fromArray([
            'id' => 'src-1',
            'label' => 'My Instructions',
            'content' => 'Be helpful.',
            'sourcePath' => '.copilot/instructions.md',
            'type' => 'repo',
            'location' => 'repository',
            'applyTo' => '**/*.php',
            'description' => 'PHP-specific instructions',
        ]);

        expect($source->id)->toBe('src-1')
            ->and($source->label)->toBe('My Instructions')
            ->and($source->content)->toBe('Be helpful.')
            ->and($source->sourcePath)->toBe('.copilot/instructions.md')
            ->and($source->type)->toBe(InstructionsSourcesType::REPO)
            ->and($source->location)->toBe(InstructionsSourcesLocation::REPOSITORY)
            ->and($source->applyTo)->toBe('**/*.php')
            ->and($source->description)->toBe('PHP-specific instructions');
    });

    it('can be created with minimal fields', function () {
        $source = InstructionsSources::fromArray([
            'id' => 'src-1',
            'label' => 'Test',
            'content' => 'content',
            'sourcePath' => 'path/to/file.md',
            'type' => 'home',
            'location' => 'user',
        ]);

        expect($source->applyTo)->toBeNull()
            ->and($source->description)->toBeNull();
    });

    it('converts to array', function () {
        $source = InstructionsSources::fromArray([
            'id' => 'src-1',
            'label' => 'Test',
            'content' => 'content',
            'sourcePath' => 'path',
            'type' => 'vscode',
            'location' => 'working-directory',
        ]);

        $array = $source->toArray();

        expect($array)->toHaveKey('id', 'src-1')
            ->and($array)->toHaveKey('type', 'vscode')
            ->and($array)->toHaveKey('location', 'working-directory')
            ->and($array)->not->toHaveKey('applyTo');
    });
});

describe('InstructionsGetSourcesResult', function () {
    it('can be created from array with sources', function () {
        $result = InstructionsGetSourcesResult::fromArray([
            'sources' => [
                [
                    'id' => 'src-1',
                    'label' => 'Instructions',
                    'content' => 'Be helpful.',
                    'sourcePath' => '.copilot/instructions.md',
                    'type' => 'repo',
                    'location' => 'repository',
                ],
            ],
        ]);

        expect($result->sources)->toHaveCount(1)
            ->and($result->sources[0])->toBeInstanceOf(InstructionsSources::class)
            ->and($result->sources[0]->id)->toBe('src-1');
    });

    it('can be created with empty sources', function () {
        $result = InstructionsGetSourcesResult::fromArray(['sources' => []]);

        expect($result->sources)->toBeEmpty();
    });

    it('converts to array', function () {
        $result = InstructionsGetSourcesResult::fromArray([
            'sources' => [
                [
                    'id' => 'src-1',
                    'label' => 'Test',
                    'content' => 'content',
                    'sourcePath' => 'path',
                    'type' => 'home',
                    'location' => 'user',
                ],
            ],
        ]);

        $array = $result->toArray();

        expect($array)->toHaveKey('sources')
            ->and($array['sources'])->toHaveCount(1)
            ->and($array['sources'][0]['id'])->toBe('src-1');
    });
});
