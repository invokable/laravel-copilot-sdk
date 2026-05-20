<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\SessionFSErrorCode;
use Revolution\Copilot\Enums\SessionFSSqliteQueryType;
use Revolution\Copilot\Types\Rpc\SessionFSError;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderCapabilities;
use Revolution\Copilot\Types\Rpc\SessionFsSetProviderRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteExistsRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteExistsResult;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteQueryRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteQueryResult;

describe('SessionFSSqliteQueryType', function () {
    it('has exec, query and run cases', function () {
        expect(SessionFSSqliteQueryType::Exec->value)->toBe('exec')
            ->and(SessionFSSqliteQueryType::Query->value)->toBe('query')
            ->and(SessionFSSqliteQueryType::Run->value)->toBe('run');
    });

    it('can be created from string value', function () {
        expect(SessionFSSqliteQueryType::from('exec'))->toBe(SessionFSSqliteQueryType::Exec)
            ->and(SessionFSSqliteQueryType::from('query'))->toBe(SessionFSSqliteQueryType::Query)
            ->and(SessionFSSqliteQueryType::from('run'))->toBe(SessionFSSqliteQueryType::Run);
    });
});

describe('SessionFsSetProviderCapabilities', function () {
    it('can be created with sqlite true', function () {
        $caps = new SessionFsSetProviderCapabilities(sqlite: true);

        expect($caps->sqlite)->toBeTrue();
    });

    it('defaults sqlite to null', function () {
        $caps = new SessionFsSetProviderCapabilities;

        expect($caps->sqlite)->toBeNull();
    });

    it('can be created from array', function () {
        $caps = SessionFsSetProviderCapabilities::fromArray(['sqlite' => true]);

        expect($caps->sqlite)->toBeTrue();
    });

    it('omits null fields in toArray', function () {
        $caps = new SessionFsSetProviderCapabilities;

        expect($caps->toArray())->toBe([]);
    });

    it('includes sqlite in toArray when set', function () {
        $caps = new SessionFsSetProviderCapabilities(sqlite: true);

        expect($caps->toArray())->toBe(['sqlite' => true]);
    });
});

describe('SessionFsSetProviderRequest with capabilities', function () {
    it('accepts capabilities parameter', function () {
        $caps = new SessionFsSetProviderCapabilities(sqlite: true);
        $request = new SessionFsSetProviderRequest(
            initialCwd: '/app',
            sessionStatePath: '.state',
            capabilities: $caps,
        );

        expect($request->capabilities)->toBeInstanceOf(SessionFsSetProviderCapabilities::class)
            ->and($request->capabilities->sqlite)->toBeTrue();
    });

    it('defaults capabilities to null', function () {
        $request = new SessionFsSetProviderRequest(
            initialCwd: '/app',
            sessionStatePath: '.state',
        );

        expect($request->capabilities)->toBeNull();
    });

    it('includes capabilities in toArray when set', function () {
        $caps = new SessionFsSetProviderCapabilities(sqlite: true);
        $request = new SessionFsSetProviderRequest(
            initialCwd: '/app',
            sessionStatePath: '.state',
            capabilities: $caps,
        );

        expect($request->toArray())->toBe([
            'initialCwd' => '/app',
            'sessionStatePath' => '.state',
            'conventions' => 'posix',
            'capabilities' => ['sqlite' => true],
        ]);
    });

    it('omits capabilities from toArray when null', function () {
        $request = new SessionFsSetProviderRequest(
            initialCwd: '/app',
            sessionStatePath: '.state',
        );

        expect($request->toArray())->toBe([
            'initialCwd' => '/app',
            'sessionStatePath' => '.state',
            'conventions' => 'posix',
        ]);
    });

    it('can be created from array with capabilities', function () {
        $request = SessionFsSetProviderRequest::fromArray([
            'initialCwd' => '/app',
            'sessionStatePath' => '.state',
            'capabilities' => ['sqlite' => true],
        ]);

        expect($request->capabilities)->toBeInstanceOf(SessionFsSetProviderCapabilities::class)
            ->and($request->capabilities->sqlite)->toBeTrue();
    });
});

describe('SessionFsSqliteExistsRequest', function () {
    it('can be created with sessionId', function () {
        $request = new SessionFsSqliteExistsRequest(sessionId: 'session-123');

        expect($request->sessionId)->toBe('session-123');
    });

    it('can be created from array', function () {
        $request = SessionFsSqliteExistsRequest::fromArray(['sessionId' => 'session-abc']);

        expect($request->sessionId)->toBe('session-abc');
    });

    it('converts to array', function () {
        $request = new SessionFsSqliteExistsRequest(sessionId: 'session-123');

        expect($request->toArray())->toBe(['sessionId' => 'session-123']);
    });
});

describe('SessionFsSqliteExistsResult', function () {
    it('can be created with exists true', function () {
        $result = new SessionFsSqliteExistsResult(exists: true);

        expect($result->exists)->toBeTrue();
    });

    it('can be created from array', function () {
        $result = SessionFsSqliteExistsResult::fromArray(['exists' => true]);

        expect($result->exists)->toBeTrue();
    });

    it('defaults exists to false', function () {
        $result = SessionFsSqliteExistsResult::fromArray([]);

        expect($result->exists)->toBeFalse();
    });

    it('converts to array', function () {
        $result = new SessionFsSqliteExistsResult(exists: true);

        expect($result->toArray())->toBe(['exists' => true]);
    });
});

describe('SessionFsSqliteQueryRequest', function () {
    it('can be created with required fields', function () {
        $request = new SessionFsSqliteQueryRequest(
            query: 'SELECT * FROM users',
            queryType: SessionFSSqliteQueryType::Query,
            sessionId: 'session-123',
        );

        expect($request->query)->toBe('SELECT * FROM users')
            ->and($request->queryType)->toBe(SessionFSSqliteQueryType::Query)
            ->and($request->sessionId)->toBe('session-123')
            ->and($request->params)->toBeNull();
    });

    it('can be created with params', function () {
        $request = new SessionFsSqliteQueryRequest(
            query: 'SELECT * FROM users WHERE id = :id',
            queryType: SessionFSSqliteQueryType::Query,
            sessionId: 'session-123',
            params: ['id' => 42.0],
        );

        expect($request->params)->toBe(['id' => 42.0]);
    });

    it('can be created from array', function () {
        $request = SessionFsSqliteQueryRequest::fromArray([
            'query' => 'INSERT INTO logs (msg) VALUES (:msg)',
            'queryType' => 'run',
            'sessionId' => 'session-xyz',
            'params' => ['msg' => 'hello'],
        ]);

        expect($request->query)->toBe('INSERT INTO logs (msg) VALUES (:msg)')
            ->and($request->queryType)->toBe(SessionFSSqliteQueryType::Run)
            ->and($request->sessionId)->toBe('session-xyz')
            ->and($request->params)->toBe(['msg' => 'hello']);
    });

    it('converts to array without params when null', function () {
        $request = new SessionFsSqliteQueryRequest(
            query: 'CREATE TABLE t (id INTEGER)',
            queryType: SessionFSSqliteQueryType::Exec,
            sessionId: 'session-123',
        );

        expect($request->toArray())->toBe([
            'query' => 'CREATE TABLE t (id INTEGER)',
            'queryType' => 'exec',
            'sessionId' => 'session-123',
        ]);
    });

    it('converts to array with params when set', function () {
        $request = new SessionFsSqliteQueryRequest(
            query: 'SELECT * FROM users WHERE name = :name',
            queryType: SessionFSSqliteQueryType::Query,
            sessionId: 'session-123',
            params: ['name' => 'Alice'],
        );

        expect($request->toArray())->toBe([
            'query' => 'SELECT * FROM users WHERE name = :name',
            'queryType' => 'query',
            'sessionId' => 'session-123',
            'params' => ['name' => 'Alice'],
        ]);
    });
});

describe('SessionFsSqliteQueryResult', function () {
    it('can be created with required fields', function () {
        $result = new SessionFsSqliteQueryResult(
            columns: ['id', 'name'],
            rows: [['id' => 1, 'name' => 'Alice']],
            rowsAffected: 0,
        );

        expect($result->columns)->toBe(['id', 'name'])
            ->and($result->rows)->toBe([['id' => 1, 'name' => 'Alice']])
            ->and($result->rowsAffected)->toBe(0)
            ->and($result->error)->toBeNull()
            ->and($result->lastInsertRowid)->toBeNull();
    });

    it('can be created with lastInsertRowid', function () {
        $result = new SessionFsSqliteQueryResult(
            columns: [],
            rows: [],
            rowsAffected: 1,
            lastInsertRowid: 42.0,
        );

        expect($result->rowsAffected)->toBe(1)
            ->and($result->lastInsertRowid)->toBe(42.0);
    });

    it('can be created with error', function () {
        $error = new SessionFSError(code: SessionFSErrorCode::UNKNOWN, message: 'SQL error');
        $result = new SessionFsSqliteQueryResult(
            columns: [],
            rows: [],
            rowsAffected: 0,
            error: $error,
        );

        expect($result->error)->toBeInstanceOf(SessionFSError::class)
            ->and($result->error->code)->toBe(SessionFSErrorCode::UNKNOWN);
    });

    it('can be created from array', function () {
        $result = SessionFsSqliteQueryResult::fromArray([
            'columns' => ['id', 'name'],
            'rows' => [['id' => 1, 'name' => 'Bob']],
            'rowsAffected' => 0,
        ]);

        expect($result->columns)->toBe(['id', 'name'])
            ->and($result->rows)->toBe([['id' => 1, 'name' => 'Bob']])
            ->and($result->rowsAffected)->toBe(0);
    });

    it('can be created from array with error', function () {
        $result = SessionFsSqliteQueryResult::fromArray([
            'columns' => [],
            'rows' => [],
            'rowsAffected' => 0,
            'error' => ['code' => 'UNKNOWN', 'message' => 'table not found'],
        ]);

        expect($result->error)->toBeInstanceOf(SessionFSError::class)
            ->and($result->error->code)->toBe(SessionFSErrorCode::UNKNOWN)
            ->and($result->error->message)->toBe('table not found');
    });

    it('converts to array without optional fields when null', function () {
        $result = new SessionFsSqliteQueryResult(
            columns: ['id'],
            rows: [],
            rowsAffected: 0,
        );

        expect($result->toArray())->toBe([
            'columns' => ['id'],
            'rows' => [],
            'rowsAffected' => 0,
        ]);
    });

    it('converts to array with all fields', function () {
        $result = new SessionFsSqliteQueryResult(
            columns: [],
            rows: [],
            rowsAffected: 1,
            lastInsertRowid: 5.0,
        );

        expect($result->toArray())->toBe([
            'columns' => [],
            'rows' => [],
            'rowsAffected' => 1,
            'lastInsertRowid' => 5.0,
        ]);
    });
});
