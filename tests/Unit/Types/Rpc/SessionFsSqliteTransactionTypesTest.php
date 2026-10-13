<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFSErrorCode;
use Revolution\Copilot\Enums\SessionFSSqliteQueryType;
use Revolution\Copilot\Enums\SessionFSSqliteTransactionErrorClass;
use Revolution\Copilot\Types\Rpc\SessionFsEntry;
use Revolution\Copilot\Types\Rpc\SessionFsError;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirResult;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirWithTypesResult;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteQueryResult;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionError;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionResult;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionStatement;
use Revolution\Copilot\Types\Rpc\SessionFsStatResult;

describe('SessionFSSqliteTransactionErrorClass', function () {
    it('has the three official classifications', function () {
        expect(SessionFSSqliteTransactionErrorClass::BusyOrLocked->value)->toBe('busyOrLocked')
            ->and(SessionFSSqliteTransactionErrorClass::Fatal->value)->toBe('fatal')
            ->and(SessionFSSqliteTransactionErrorClass::PostCommitAmbiguous->value)->toBe('postCommitAmbiguous')
            ->and(SessionFSSqliteTransactionErrorClass::from('busyOrLocked'))->toBe(SessionFSSqliteTransactionErrorClass::BusyOrLocked);
    });
});

describe('SessionFsSqliteTransactionStatement', function () {
    it('round-trips with params', function () {
        $data = ['query' => 'INSERT INTO t VALUES (:a)', 'queryType' => 'run', 'params' => ['a' => 1, 'b' => null]];
        $statement = SessionFsSqliteTransactionStatement::fromArray($data);

        expect($statement->query)->toBe('INSERT INTO t VALUES (:a)')
            ->and($statement->queryType)->toBe(SessionFSSqliteQueryType::Run)
            ->and($statement->params)->toBe(['a' => 1, 'b' => null])
            ->and($statement->toArray())->toBe($data);
    });

    it('omits params when null', function () {
        $data = ['query' => 'SELECT 1', 'queryType' => 'query'];

        expect(SessionFsSqliteTransactionStatement::fromArray($data)->toArray())->toBe($data);
    });
});

describe('SessionFsSqliteTransactionRequest', function () {
    it('round-trips statements', function () {
        $data = [
            'sessionId' => 'session-1',
            'statements' => [
                ['query' => 'CREATE TABLE t (a)', 'queryType' => 'exec'],
                ['query' => 'INSERT INTO t VALUES (:a)', 'queryType' => 'run', 'params' => ['a' => 'x']],
            ],
        ];
        $request = SessionFsSqliteTransactionRequest::fromArray($data);

        expect($request->sessionId)->toBe('session-1')
            ->and($request->statements)->toHaveCount(2)
            ->and($request->statements[0])->toBeInstanceOf(SessionFsSqliteTransactionStatement::class)
            ->and($request->toArray())->toBe($data);
    });

    it('defaults to no statements', function () {
        expect(SessionFsSqliteTransactionRequest::fromArray(['sessionId' => 's'])->toArray())
            ->toBe(['sessionId' => 's', 'statements' => []]);
    });
});

describe('SessionFsSqliteTransactionError', function () {
    it('round-trips known classifications', function () {
        foreach (['busyOrLocked', 'fatal', 'postCommitAmbiguous'] as $class) {
            $data = ['errorClass' => $class, 'message' => 'failed'];
            $error = SessionFsSqliteTransactionError::fromArray($data);

            expect($error->errorClass)->toBe(SessionFSSqliteTransactionErrorClass::from($class))
                ->and($error->toArray())->toBe($data);
        }
    });

    it('preserves unknown classifications as strings', function () {
        $data = ['errorClass' => 'future', 'message' => 'm'];

        expect(SessionFsSqliteTransactionError::fromArray($data)->errorClass)->toBe('future')
            ->and(SessionFsSqliteTransactionError::fromArray($data)->toArray())->toBe($data);
    });
});

describe('SessionFsSqliteTransactionResult', function () {
    it('round-trips results', function () {
        $data = ['results' => [
            ['columns' => ['id'], 'rows' => [['id' => 1]], 'rowsAffected' => 0],
            ['columns' => [], 'rows' => [], 'rowsAffected' => 1, 'lastInsertRowid' => 5.0],
        ]];
        $result = SessionFsSqliteTransactionResult::fromArray($data);

        expect($result->results[0])->toBeInstanceOf(SessionFsSqliteQueryResult::class)
            ->and($result->error)->toBeNull()
            ->and($result->toArray())->toBe($data);
    });

    it('round-trips a classified error', function () {
        $data = ['results' => [], 'error' => ['errorClass' => 'busyOrLocked', 'message' => 'locked']];
        $result = SessionFsSqliteTransactionResult::fromArray($data);

        expect($result->error)->toBeInstanceOf(SessionFsSqliteTransactionError::class)
            ->and($result->toArray())->toBe($data);
    });
});

describe('SessionFs result errors', function () {
    it('round-trips stat results with an error', function () {
        $data = [
            'birthtime' => 'b', 'isDirectory' => false, 'isFile' => false, 'mtime' => 'm', 'size' => 0,
            'error' => ['code' => 'ENOENT', 'message' => 'missing'],
        ];
        $result = SessionFsStatResult::fromArray($data);

        expect($result->error)->toBeInstanceOf(SessionFsError::class)
            ->and($result->error->code)->toBe(SessionFSErrorCode::ENOENT)
            ->and($result->toArray())->toBe($data);
    });

    it('round-trips readdir results with an error', function () {
        $data = ['entries' => [], 'error' => ['code' => 'UNKNOWN', 'message' => 'boom']];

        expect(SessionFsReaddirResult::fromArray($data)->toArray())->toBe($data)
            ->and(SessionFsReaddirResult::fromArray(['entries' => ['a']])->toArray())->toBe(['entries' => ['a']]);
    });

    it('round-trips readdirWithTypes results with an error', function () {
        $data = ['entries' => [], 'error' => ['code' => 'ENOENT', 'message' => 'gone']];
        $withEntries = ['entries' => [['name' => 'a', 'type' => 'file']]];

        expect(SessionFsReaddirWithTypesResult::fromArray($data)->toArray())->toBe($data)
            ->and(SessionFsReaddirWithTypesResult::fromArray($withEntries)->entries[0])->toBeInstanceOf(SessionFsEntry::class)
            ->and(SessionFsReaddirWithTypesResult::fromArray($withEntries)->toArray())->toBe($withEntries);
    });
});
