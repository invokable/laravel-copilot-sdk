<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Hooks\SubagentStopHookOutput;

describe('SubagentStopHookOutput', function () {
    it('serializes a blocking response with its reason', function () {
        $output = SubagentStopHookOutput::fromArray([
            'decision' => 'block',
            'reason' => 'Address the missing test first.',
        ]);

        expect($output->toArray())->toBe([
            'decision' => 'block',
            'reason' => 'Address the missing test first.',
        ]);
    });

    it('serializes a replacement response without a decision', function () {
        $output = new SubagentStopHookOutput(modifiedResponse: 'Reviewed and ready.');

        expect($output->toArray())->toBe([
            'modifiedResponse' => 'Reviewed and ready.',
        ]);
    });
});
