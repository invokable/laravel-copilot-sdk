<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\PreMcpToolCallHookOutput;

describe('PreMcpToolCallHookOutput', function () {
    it('can be created with null metaToUse', function () {
        $output = new PreMcpToolCallHookOutput;

        expect($output->metaToUse)->toBeNull();
    });

    it('can be created with empty array metaToUse', function () {
        $output = new PreMcpToolCallHookOutput(
            metaToUse: [],
        );

        expect($output->metaToUse)->toBe([]);
    });

    it('can be created with metaToUse data', function () {
        $output = new PreMcpToolCallHookOutput(
            metaToUse: ['traceId' => 'abc-123', 'source' => 'test'],
        );

        expect($output->metaToUse)->toBe(['traceId' => 'abc-123', 'source' => 'test']);
    });

    it('can be created from array with no metaToUse', function () {
        $output = PreMcpToolCallHookOutput::fromArray([]);

        expect($output->metaToUse)->toBeNull();
    });

    it('can be created from array with metaToUse', function () {
        $output = PreMcpToolCallHookOutput::fromArray([
            'metaToUse' => ['key' => 'value'],
        ]);

        expect($output->metaToUse)->toBe(['key' => 'value']);
    });

    it('converts to empty array when metaToUse is null', function () {
        $output = new PreMcpToolCallHookOutput;

        expect($output->toArray())->toBe([]);
    });

    it('converts to array with empty metaToUse array', function () {
        $output = new PreMcpToolCallHookOutput(metaToUse: []);

        expect($output->toArray())->toBe(['metaToUse' => []]);
    });

    it('converts to array with metaToUse data', function () {
        $output = new PreMcpToolCallHookOutput(
            metaToUse: ['userId' => '42', 'role' => 'admin'],
        );

        expect($output->toArray())->toBe([
            'metaToUse' => ['userId' => '42', 'role' => 'admin'],
        ]);
    });

    it('roundtrips through array conversion', function () {
        $original = new PreMcpToolCallHookOutput(
            metaToUse: ['test' => 'data'],
        );

        $recreated = PreMcpToolCallHookOutput::fromArray($original->toArray());

        expect($recreated->metaToUse)->toBe($original->metaToUse);
    });
});
