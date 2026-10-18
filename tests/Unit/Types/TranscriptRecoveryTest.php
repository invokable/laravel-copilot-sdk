<?php

declare(strict_types=1);

use Revolution\Copilot\Types\TranscriptRecovery;

it('round trips transcript recovery details', function () {
    $recovery = TranscriptRecovery::fromArray([
        'plannedBackupPath' => '/tmp/transcript.jsonl.bak',
        'invalidLineNumbers' => [2, 5],
        'sessionStartMoved' => true,
    ]);

    expect($recovery->toArray())->toBe([
        'plannedBackupPath' => '/tmp/transcript.jsonl.bak',
        'invalidLineNumbers' => [2, 5],
        'sessionStartMoved' => true,
    ]);
});
