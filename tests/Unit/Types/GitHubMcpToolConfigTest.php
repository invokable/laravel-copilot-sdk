<?php

declare(strict_types=1);

use Revolution\Copilot\Types\GitHubMcpToolConfig;

describe('GitHubMcpToolConfig', function () {
    it('can be created with default values', function () {
        $config = new GitHubMcpToolConfig;

        expect($config->enableAllTools)->toBeNull()
            ->and($config->additionalToolsets)->toBeNull()
            ->and($config->additionalTools)->toBeNull()
            ->and($config->enableInsidersMode)->toBeNull()
            ->and($config->disableFormDeferral)->toBeNull();
    });

    it('can be created with all values', function () {
        $config = new GitHubMcpToolConfig(
            enableAllTools: true,
            additionalToolsets: ['issues'],
            additionalTools: ['create_issue'],
            enableInsidersMode: true,
            disableFormDeferral: true,
        );

        expect($config->enableAllTools)->toBeTrue()
            ->and($config->additionalToolsets)->toBe(['issues'])
            ->and($config->additionalTools)->toBe(['create_issue'])
            ->and($config->enableInsidersMode)->toBeTrue()
            ->and($config->disableFormDeferral)->toBeTrue();
    });

    it('can be created from array', function () {
        $config = GitHubMcpToolConfig::fromArray([
            'enableAllTools' => true,
            'additionalToolsets' => ['issues'],
            'additionalTools' => ['create_issue'],
            'enableInsidersMode' => false,
            'disableFormDeferral' => false,
        ]);

        expect($config->enableAllTools)->toBeTrue()
            ->and($config->additionalToolsets)->toBe(['issues'])
            ->and($config->additionalTools)->toBe(['create_issue'])
            ->and($config->enableInsidersMode)->toBeFalse()
            ->and($config->disableFormDeferral)->toBeFalse();
    });

    it('can be created from an empty array', function () {
        $config = GitHubMcpToolConfig::fromArray([]);

        expect($config->enableAllTools)->toBeNull()
            ->and($config->additionalToolsets)->toBeNull()
            ->and($config->additionalTools)->toBeNull()
            ->and($config->enableInsidersMode)->toBeNull()
            ->and($config->disableFormDeferral)->toBeNull();
    });

    it('converts to array excluding null values', function () {
        $config = new GitHubMcpToolConfig(enableAllTools: true);

        expect($config->toArray())->toBe(['enableAllTools' => true]);
    });

    it('roundtrips through array conversion', function () {
        $original = new GitHubMcpToolConfig(
            enableAllTools: true,
            additionalToolsets: ['issues', 'pull_requests'],
            additionalTools: ['create_issue'],
            enableInsidersMode: true,
            disableFormDeferral: false,
        );

        $roundTripped = GitHubMcpToolConfig::fromArray($original->toArray());

        expect($roundTripped->toArray())->toBe($original->toArray());
    });
});
