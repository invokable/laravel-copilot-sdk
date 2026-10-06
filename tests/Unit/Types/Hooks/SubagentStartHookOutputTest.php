<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\SubagentStartHookOutput;

describe('SubagentStartHookOutput', function () {
    it('serializes context for the sub-agent prompt', function () {
        $output = SubagentStartHookOutput::fromArray([
            'additionalContext' => 'Use the project conventions.',
        ]);

        expect($output->toArray())->toBe([
            'additionalContext' => 'Use the project conventions.',
        ]);
    });

    it('omits context when absent', function () {
        expect((new SubagentStartHookOutput)->toArray())->toBe([]);
    });
});
