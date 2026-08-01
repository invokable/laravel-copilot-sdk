<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\HistoryFileRestoreSkipReason;
use Revolution\Copilot\Enums\HistoryRewindChangeType;
use Revolution\Copilot\Enums\HistoryRewindMode;
use Revolution\Copilot\Enums\HistoryRewindOutcome;
use Revolution\Copilot\Enums\HistoryRewindUnavailableReason;
use Revolution\Copilot\Types\Rpc\HistoryCompactRequest;
use Revolution\Copilot\Types\Rpc\HistoryListRewindPointsResult;
use Revolution\Copilot\Types\Rpc\HistoryPreviewRewindRequest;
use Revolution\Copilot\Types\Rpc\HistoryPreviewRewindResult;
use Revolution\Copilot\Types\Rpc\HistoryRewindFilePreview;
use Revolution\Copilot\Types\Rpc\HistoryRewindPoint;
use Revolution\Copilot\Types\Rpc\HistoryRewindRequest;
use Revolution\Copilot\Types\Rpc\HistoryRewindResult;
use Revolution\Copilot\Types\Rpc\HistorySkippedFileRestore;

describe('HistoryCompactRequest', function () {
    it('keeps only provided fields', function () {
        $request = HistoryCompactRequest::fromArray([
            'trigger' => 'model_switch',
            'tokenLimit' => 8000,
        ]);

        expect($request->trigger)->toBe('model_switch')
            ->and($request->toArray())->toBe(['trigger' => 'model_switch', 'tokenLimit' => 8000]);
    });

    it('supports an empty payload', function () {
        expect(HistoryCompactRequest::fromArray([])->toArray())->toBe([]);
    });
});

describe('HistoryRewindRequest', function () {
    it('resolves the mode enum', function () {
        $request = HistoryRewindRequest::fromArray([
            'eventId' => 'e-1',
            'mode' => 'conversation-and-files',
        ]);

        expect($request->mode)->toBe(HistoryRewindMode::CONVERSATION_AND_FILES)
            ->and($request->toArray())->toBe(['eventId' => 'e-1', 'mode' => 'conversation-and-files']);
    });
});

describe('HistoryRewindPoint', function () {
    it('creates from array', function () {
        $point = HistoryRewindPoint::fromArray([
            'eventId' => 'e-1',
            'userMessage' => 'hi',
            'timestamp' => '2026-01-01T00:00:00Z',
            'canRestoreFiles' => true,
            'fileCount' => 2,
            'turnChangedFiles' => true,
            'linesAdded' => 5,
            'linesRemoved' => 1,
            'isAutopilotContinuation' => false,
        ]);

        expect($point->eventId)->toBe('e-1')
            ->and($point->canRestoreFiles)->toBeTrue()
            ->and($point->toArray()['fileCount'])->toBe(2);
    });
});

describe('HistoryListRewindPointsResult', function () {
    it('hydrates points and unavailable reason', function () {
        $result = HistoryListRewindPointsResult::fromArray([
            'fileChangeTrackingEnabled' => false,
            'unavailableReason' => 'session-busy',
            'points' => [],
        ]);

        expect($result->fileChangeTrackingEnabled)->toBeFalse()
            ->and($result->unavailableReason)->toBe(HistoryRewindUnavailableReason::SESSION_BUSY)
            ->and($result->toArray()['unavailableReason'])->toBe('session-busy');
    });
});

describe('HistoryRewindResult', function () {
    it('carries outcome and skipped files', function () {
        $result = HistoryRewindResult::fromArray([
            'outcome' => 'files-rolled-back',
            'restoredFiles' => ['a.php'],
            'skippedFiles' => [['path' => 'b.php', 'reason' => 'user-modified']],
            'eventsRemoved' => 3,
        ]);

        expect($result->outcome)->toBe(HistoryRewindOutcome::FILES_ROLLED_BACK)
            ->and($result->skippedFiles[0])->toBeInstanceOf(HistorySkippedFileRestore::class)
            ->and($result->skippedFiles[0]->reason)->toBe(HistoryFileRestoreSkipReason::USER_MODIFIED)
            ->and($result->toArray()['skippedFiles'][0])->toBe(['path' => 'b.php', 'reason' => 'user-modified']);
    });
});

describe('HistoryPreviewRewindRequest', function () {
    it('roundtrips', function () {
        expect(HistoryPreviewRewindRequest::fromArray(['eventId' => 'e-1'])->toArray())->toBe(['eventId' => 'e-1']);
    });
});

describe('HistoryPreviewRewindResult', function () {
    it('hydrates file previews', function () {
        $result = HistoryPreviewRewindResult::fromArray([
            'available' => true,
            'fileCount' => 1,
            'files' => [['path' => 'a.php', 'changeType' => 'modified', 'linesAdded' => 2, 'linesRemoved' => 1]],
        ]);

        expect($result->files[0])->toBeInstanceOf(HistoryRewindFilePreview::class)
            ->and($result->files[0]->changeType)->toBe(HistoryRewindChangeType::MODIFIED)
            ->and($result->toArray()['files'][0]['changeType'])->toBe('modified');
    });
});
