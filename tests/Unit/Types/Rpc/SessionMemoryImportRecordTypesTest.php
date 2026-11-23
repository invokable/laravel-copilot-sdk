<?php

declare(strict_types=1);

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\MemoryImportScope;
use Revolution\Copilot\Enums\MemoryImportStatus;
use Revolution\Copilot\Types\Rpc\SessionMemoryImportRecordRequest;
use Revolution\Copilot\Types\Rpc\SessionMemoryImportRecordResult;

describe('Session memory import types', function () {
    it('round trips request scopes including unknown values', function (string $scope, MemoryImportScope|string $expected) {
        $data = ['scope' => $scope, 'subject' => 'subject', 'fact' => 'fact', 'reason' => 'reason', 'citations' => 'source'];
        $request = SessionMemoryImportRecordRequest::fromArray($data);

        expect($request)->toBeInstanceOf(Arrayable::class)
            ->and($request->scope)->toBe($expected)
            ->and($request->toArray())->toBe($data);
    })->with([
        ['user', MemoryImportScope::User],
        ['repository', MemoryImportScope::Repository],
        ['future-scope', 'future-scope'],
    ]);

    it('round trips result statuses including unknown values', function (string $status, MemoryImportStatus|string $expected) {
        $data = ['ok' => false, 'status' => $status, 'code' => 'code', 'message' => 'message'];
        $result = SessionMemoryImportRecordResult::fromArray($data);

        expect($result)->toBeInstanceOf(Arrayable::class)
            ->and($result->status)->toBe($expected)
            ->and($result->toArray())->toBe($data);
    })->with([
        ['imported', MemoryImportStatus::Imported],
        ['conflict', MemoryImportStatus::Conflict],
        ['disabled', MemoryImportStatus::Disabled],
        ['unavailable', MemoryImportStatus::Unavailable],
        ['failed', MemoryImportStatus::Failed],
        ['future-status', 'future-status'],
    ]);
});
