<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FileLogLevel;
use Revolution\Copilot\Enums\FileLogOperation;
use Revolution\Copilot\Types\Rpc\LoggingFileCloseRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileFlushRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileOpenRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileOpenResult;
use Revolution\Copilot\Types\Rpc\LoggingFileWriteRequest;

describe('LoggingFileOpenRequest', function () {
    it('round trips the logger configuration', function () {
        $data = [
            'level' => 'debug',
            'debugEnvironmentVariables' => ['APP_DEBUG', 'COPILOT_DEBUG'],
            'secretValues' => ['secret-one', 'secret-two'],
        ];

        $request = LoggingFileOpenRequest::fromArray($data);

        expect($request->level)->toBe(FileLogLevel::Debug)
            ->and($request->toArray())->toBe($data);
    });

    it('omits logger configuration options that are not set', function () {
        expect(LoggingFileOpenRequest::fromArray([])->toArray())->toBe([]);
    });
});

describe('LoggingFileOpenResult', function () {
    it('round trips the opaque logger identifier', function () {
        $result = LoggingFileOpenResult::fromArray(['logId' => 'file-log-1']);

        expect($result->logId)->toBe('file-log-1')
            ->and($result->toArray())->toBe(['logId' => 'file-log-1']);
    });
});

describe('LoggingFileWriteRequest', function () {
    it('round trips an operation and optional group level', function () {
        $data = [
            'logId' => 'file-log-1',
            'operation' => 'startGroup',
            'message' => 'Working',
            'groupLevel' => 'warning',
        ];
        $request = LoggingFileWriteRequest::fromArray($data);

        expect($request->operation)->toBe(FileLogOperation::StartGroup)
            ->and($request->groupLevel)->toBe(FileLogLevel::Warning)
            ->and($request->toArray())->toBe($data);
    });

    it('omits an unset group level', function () {
        $request = new LoggingFileWriteRequest(
            logId: 'file-log-2',
            operation: FileLogOperation::EndGroup,
            message: '',
        );

        expect($request->toArray())->toBe([
            'logId' => 'file-log-2',
            'operation' => 'endGroup',
            'message' => '',
        ]);
    });
});

describe('LoggingFileFlushRequest', function () {
    it('round trips the opaque logger identifier', function () {
        $request = LoggingFileFlushRequest::fromArray(['logId' => 'file-log-1']);

        expect($request->toArray())->toBe(['logId' => 'file-log-1']);
    });
});

describe('LoggingFileCloseRequest', function () {
    it('round trips the opaque logger identifier', function () {
        $request = LoggingFileCloseRequest::fromArray(['logId' => 'file-log-1']);

        expect($request->toArray())->toBe(['logId' => 'file-log-1']);
    });
});

describe('File logging enums', function () {
    it('contains the upstream file log level and operation literals', function () {
        expect(array_column(FileLogLevel::cases(), 'value'))->toBe([
            'none',
            'error',
            'warning',
            'info',
            'debug',
        ])->and(array_column(FileLogOperation::cases(), 'value'))->toBe([
            'log',
            'debug',
            'info',
            'notice',
            'warning',
            'error',
            'startGroup',
            'endGroup',
        ]);
    });
});
