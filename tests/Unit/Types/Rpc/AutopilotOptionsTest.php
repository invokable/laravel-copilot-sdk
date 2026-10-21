<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\AutopilotOptions;

describe('AutopilotOptions', function () {
    it('preserves explicit false and zero while omitting unset values', function () {
        $default = new AutopilotOptions;
        $options = AutopilotOptions::fromArray([
            'continuationEnabled' => false,
            'maxContinues' => 0,
        ]);
        $unsetOptions = AutopilotOptions::fromArray([
            'continuationEnabled' => null,
            'maxContinues' => null,
        ]);

        expect($default->toArray())->toBe([])
            ->and($unsetOptions->toArray())->toBe([])
            ->and($options->continuationEnabled)->toBeFalse()
            ->and($options->maxContinues)->toBe(0)
            ->and($options->toArray())->toBe([
                'continuationEnabled' => false,
                'maxContinues' => 0,
            ]);
    });
});
