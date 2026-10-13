<?php

declare(strict_types=1);

use Revolution\Copilot\Contracts\SessionFsBinaryProvider;
use Revolution\Copilot\Contracts\SessionFsFilesystemProvider;
use Revolution\Copilot\Contracts\SessionFsProvider;
use Revolution\Copilot\Contracts\SessionFsSqliteCapableProvider;
use Revolution\Copilot\Contracts\SessionFsSqliteProvider;
use Revolution\Copilot\Contracts\SessionFsSqliteTransactionProvider;
use Revolution\Copilot\Enums\EntryType;
use Revolution\Copilot\Enums\SessionFSSqliteQueryType;
use Revolution\Copilot\Enums\SessionFSSqliteTransactionErrorClass;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Exceptions\SessionFsSqliteTransactionFailure;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Session;
use Revolution\Copilot\Types\Rpc\SessionFsAppendFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsEntry;
use Revolution\Copilot\Types\Rpc\SessionFsExistsRequest;
use Revolution\Copilot\Types\Rpc\SessionFsMkdirRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirWithTypesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsRenameRequest;
use Revolution\Copilot\Types\Rpc\SessionFsRmRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteExistsRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteQueryRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteQueryResult;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionRequest;
use Revolution\Copilot\Types\Rpc\SessionFsSqliteTransactionStatement;
use Revolution\Copilot\Types\Rpc\SessionFsStatRequest;
use Revolution\Copilot\Types\Rpc\SessionFsStatResult;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileRequest;

/**
 * An exception carrying a Node-style string error code.
 */
class SessionFsTestErrnoException extends RuntimeException
{
    public function __construct(string $message, public $code = 'ENOENT') // @phpstan-ignore-line
    {
        parent::__construct($message);
    }
}

/**
 * Records calls and optionally throws, so one provider can serve every test.
 */
class SessionFsTestProvider implements SessionFsFilesystemProvider, SessionFsSqliteCapableProvider
{
    /** @var list<array{string, array}> */
    public array $calls = [];

    public ?Throwable $failure = null;

    public bool $exists = true;

    public ?SessionFsSqliteProvider $sqlite = null;

    public function __construct()
    {
        $this->sqlite = new SessionFsTestSqlite($this);
    }

    public function record(string $method, array $args): void
    {
        $this->calls[] = [$method, $args];

        if ($this->failure !== null) {
            throw $this->failure;
        }
    }

    public function readFile(string $path): string
    {
        $this->record('readFile', [$path]);

        return 'content';
    }

    public function writeFile(string $path, string $content, ?int $mode = null): void
    {
        $this->record('writeFile', [$path, $content, $mode]);
    }

    public function appendFile(string $path, string $content, ?int $mode = null): void
    {
        $this->record('appendFile', [$path, $content, $mode]);
    }

    public function exists(string $path): bool
    {
        $this->record('exists', [$path]);

        return $this->exists;
    }

    public function stat(string $path): SessionFsStatResult
    {
        $this->record('stat', [$path]);

        return new SessionFsStatResult('2026-01-01T00:00:00.000Z', false, true, '2026-01-02T00:00:00.000Z', 42);
    }

    public function mkdir(string $path, bool $recursive = false, ?int $mode = null): void
    {
        $this->record('mkdir', [$path, $recursive, $mode]);
    }

    public function readdir(string $path): array
    {
        $this->record('readdir', [$path]);

        return ['a.txt', 'dir'];
    }

    public function readdirWithTypes(string $path): array
    {
        $this->record('readdirWithTypes', [$path]);

        return [new SessionFsEntry('a.txt', EntryType::File), ['name' => 'dir', 'type' => 'directory']];
    }

    public function rm(string $path, bool $recursive = false, bool $force = false): void
    {
        $this->record('rm', [$path, $recursive, $force]);
    }

    public function rename(string $src, string $dest): void
    {
        $this->record('rename', [$src, $dest]);
    }

    public function sqlite(): SessionFsSqliteProvider
    {
        return $this->sqlite;
    }
}

class SessionFsTestSqlite implements SessionFsSqliteTransactionProvider
{
    public mixed $queryResult = null;

    public mixed $transactionResult = [];

    public function __construct(private SessionFsTestProvider $owner) {}

    public function query(SessionFSSqliteQueryType $queryType, string $query, ?array $params = null): SessionFsSqliteQueryResult|array|null
    {
        $this->owner->record('sqlite.query', [$queryType, $query, $params]);

        return $this->queryResult;
    }

    public function exists(): bool
    {
        $this->owner->record('sqlite.exists', []);

        return true;
    }

    public function transaction(array $statements): array
    {
        $this->owner->record('sqlite.transaction', [$statements]);

        return $this->transactionResult;
    }
}

function sessionFsTestSession(SessionFsProvider|array|null $provider): Session
{
    $session = new Session('session-1', Mockery::mock(JsonRpcClient::class));
    $session->registerSessionFsProvider($provider);

    return $session;
}

/** A provider that only has the original two methods. */
function sessionFsLegacyProvider(): SessionFsProvider
{
    return new class implements SessionFsProvider
    {
        public function readFile(string $path): string
        {
            return 'legacy';
        }

        public function writeFile(string $path, string $content, ?int $mode = null): void {}
    };
}

describe('SessionFs extended filesystem callbacks', function () {
    it('appends to files', function () {
        $provider = new SessionFsTestProvider;
        $session = sessionFsTestSession($provider);

        $result = $session->handleSessionFsAppendFile(new SessionFsAppendFileRequest('more', '/a.log', 'session-1', 0o644));

        expect($result)->toBe([])
            ->and($provider->calls)->toBe([['appendFile', ['/a.log', 'more', 0o644]]]);
    });

    it('checks existence and reports false on any failure', function () {
        $provider = new SessionFsTestProvider;
        $session = sessionFsTestSession($provider);

        expect($session->handleSessionFsExists(new SessionFsExistsRequest('/a', 'session-1')))->toBe(['exists' => true]);

        $provider->exists = false;
        expect($session->handleSessionFsExists(new SessionFsExistsRequest('/a', 'session-1')))->toBe(['exists' => false]);

        $provider->failure = new RuntimeException('boom');
        expect($session->handleSessionFsExists(new SessionFsExistsRequest('/a', 'session-1')))->toBe(['exists' => false]);
    });

    it('returns stat metadata', function () {
        $session = sessionFsTestSession(new SessionFsTestProvider);

        expect($session->handleSessionFsStat(new SessionFsStatRequest('/a', 'session-1')))->toBe([
            'birthtime' => '2026-01-01T00:00:00.000Z',
            'isDirectory' => false,
            'isFile' => true,
            'mtime' => '2026-01-02T00:00:00.000Z',
            'size' => 42,
        ]);
    });

    it('returns zero values plus the error when stat fails', function () {
        $provider = new SessionFsTestProvider;
        $provider->failure = new SessionFsTestErrnoException('missing');
        $session = sessionFsTestSession($provider);

        $result = $session->handleSessionFsStat(new SessionFsStatRequest('/missing', 'session-1'));

        expect($result['isFile'])->toBeFalse()
            ->and($result['isDirectory'])->toBeFalse()
            ->and($result['size'])->toBe(0)
            ->and($result['mtime'])->toBeString()
            ->and($result['birthtime'])->toBeString()
            ->and($result['error'])->toBe(['code' => 'ENOENT', 'message' => 'missing']);
    });

    it('creates directories with recursive and mode defaults', function () {
        $provider = new SessionFsTestProvider;
        $session = sessionFsTestSession($provider);

        $session->handleSessionFsMkdir(new SessionFsMkdirRequest('/a', 'session-1', 0o755, true));
        $session->handleSessionFsMkdir(new SessionFsMkdirRequest('/b', 'session-1'));

        expect($provider->calls)->toBe([['mkdir', ['/a', true, 0o755]], ['mkdir', ['/b', false, null]]]);
    });

    it('lists directories and entries with types', function () {
        $session = sessionFsTestSession(new SessionFsTestProvider);

        expect($session->handleSessionFsReaddir(new SessionFsReaddirRequest('/', 'session-1')))
            ->toBe(['entries' => ['a.txt', 'dir']])
            ->and($session->handleSessionFsReaddirWithTypes(new SessionFsReaddirWithTypesRequest('/', 'session-1')))
            ->toBe(['entries' => [['name' => 'a.txt', 'type' => 'file'], ['name' => 'dir', 'type' => 'directory']]]);
    });

    it('returns empty entries plus an error when listing fails', function () {
        $provider = new SessionFsTestProvider;
        $provider->failure = new SessionFsTestErrnoException('gone');
        $session = sessionFsTestSession($provider);

        expect($session->handleSessionFsReaddir(new SessionFsReaddirRequest('/', 'session-1')))
            ->toBe(['entries' => [], 'error' => ['code' => 'ENOENT', 'message' => 'gone']])
            ->and($session->handleSessionFsReaddirWithTypes(new SessionFsReaddirWithTypesRequest('/', 'session-1')))
            ->toBe(['entries' => [], 'error' => ['code' => 'ENOENT', 'message' => 'gone']]);
    });

    it('removes and renames paths', function () {
        $provider = new SessionFsTestProvider;
        $session = sessionFsTestSession($provider);

        $rm = $session->handleSessionFsRm(new SessionFsRmRequest('/a', 'session-1', true, true));
        $rmDefaults = $session->handleSessionFsRm(new SessionFsRmRequest('/b', 'session-1'));
        $rename = $session->handleSessionFsRename(new SessionFsRenameRequest('/a', '/b', 'session-1'));

        expect($rm)->toBe([])->and($rmDefaults)->toBe([])->and($rename)->toBe([])
            ->and($provider->calls)->toBe([
                ['rm', ['/a', true, true]],
                ['rm', ['/b', false, false]],
                ['rename', ['/a', '/b']],
            ]);
    });

    it('maps ENOENT and other failures to SessionFs error codes', function (Throwable $failure, string $code) {
        $provider = new SessionFsTestProvider;
        $provider->failure = $failure;
        $session = sessionFsTestSession($provider);

        $expected = ['code' => $code, 'message' => $failure->getMessage()];

        expect($session->handleSessionFsAppendFile(new SessionFsAppendFileRequest('x', '/a', 'session-1')))->toBe($expected)
            ->and($session->handleSessionFsMkdir(new SessionFsMkdirRequest('/a', 'session-1')))->toBe($expected)
            ->and($session->handleSessionFsRm(new SessionFsRmRequest('/a', 'session-1')))->toBe($expected)
            ->and($session->handleSessionFsRename(new SessionFsRenameRequest('/a', '/b', 'session-1')))->toBe($expected)
            ->and($session->handleSessionFsWriteFile(new SessionFsWriteFileRequest('x', '/a', 'session-1')))->toBe($expected);
    })->with([
        'string ENOENT code' => [fn () => new SessionFsTestErrnoException('no such file'), 'ENOENT'],
        'errno 2' => [fn () => new RuntimeException('no such file', 2), 'ENOENT'],
        'other code' => [fn () => new SessionFsTestErrnoException('denied', 'EACCES'), 'UNKNOWN'],
        'no code' => [fn () => new RuntimeException('failed'), 'UNKNOWN'],
    ]);

    it('reports unsupported operations for providers that only implement the original two methods', function () {
        $session = sessionFsTestSession(sessionFsLegacyProvider());

        $unsupported = fn (string $operation) => ['code' => 'UNKNOWN', 'message' => "sessionFs.{$operation} is not supported by this provider"];

        expect($session->handleSessionFsAppendFile(new SessionFsAppendFileRequest('x', '/a', 'session-1')))->toBe($unsupported('appendFile'))
            ->and($session->handleSessionFsExists(new SessionFsExistsRequest('/a', 'session-1')))->toBe(['exists' => false])
            ->and($session->handleSessionFsStat(new SessionFsStatRequest('/a', 'session-1')))->toMatchArray([
                'isFile' => false,
                'isDirectory' => false,
                'size' => 0,
                'error' => ['code' => 'UNKNOWN', 'message' => 'sessionFs.stat is not supported by this provider'],
            ])
            ->and($session->handleSessionFsMkdir(new SessionFsMkdirRequest('/a', 'session-1')))->toBe($unsupported('mkdir'))
            ->and($session->handleSessionFsReaddir(new SessionFsReaddirRequest('/a', 'session-1')))->toBe([
                'entries' => [], 'error' => ['code' => 'UNKNOWN', 'message' => 'sessionFs.readdir is not supported by this provider'],
            ])
            ->and($session->handleSessionFsReaddirWithTypes(new SessionFsReaddirWithTypesRequest('/a', 'session-1')))->toBe([
                'entries' => [], 'error' => ['code' => 'UNKNOWN', 'message' => 'sessionFs.readdirWithTypes is not supported by this provider'],
            ])
            ->and($session->handleSessionFsRm(new SessionFsRmRequest('/a', 'session-1')))->toBe($unsupported('rm'))
            ->and($session->handleSessionFsRename(new SessionFsRenameRequest('/a', '/b', 'session-1')))->toBe($unsupported('rename'));
    });

    it('supports array providers with optional callables', function () {
        $calls = [];
        $session = sessionFsTestSession([
            'readFile' => fn () => '',
            'writeFile' => fn () => null,
            'appendFile' => function (string $path, string $content, ?int $mode) use (&$calls) {
                $calls[] = ['appendFile', $path, $content, $mode];
            },
            'exists' => fn (string $path) => $path === '/yes',
            'stat' => fn () => ['birthtime' => 'b', 'mtime' => 'm', 'isFile' => true, 'isDirectory' => false, 'size' => 3],
            'mkdir' => function (string $path, bool $recursive, ?int $mode) use (&$calls) {
                $calls[] = ['mkdir', $path, $recursive, $mode];
            },
            'readdir' => fn () => ['x'],
            'readdirWithTypes' => fn () => [['name' => 'x', 'type' => 'file']],
            'rm' => function (string $path, bool $recursive, bool $force) use (&$calls) {
                $calls[] = ['rm', $path, $recursive, $force];
            },
            'rename' => function (string $src, string $dest) use (&$calls) {
                $calls[] = ['rename', $src, $dest];
            },
        ]);

        expect($session->handleSessionFsAppendFile(new SessionFsAppendFileRequest('c', '/a', 'session-1')))->toBe([])
            ->and($session->handleSessionFsExists(new SessionFsExistsRequest('/yes', 'session-1')))->toBe(['exists' => true])
            ->and($session->handleSessionFsExists(new SessionFsExistsRequest('/no', 'session-1')))->toBe(['exists' => false])
            ->and($session->handleSessionFsStat(new SessionFsStatRequest('/a', 'session-1')))->toBe([
                'birthtime' => 'b', 'isDirectory' => false, 'isFile' => true, 'mtime' => 'm', 'size' => 3,
            ])
            ->and($session->handleSessionFsMkdir(new SessionFsMkdirRequest('/d', 'session-1', null, true)))->toBe([])
            ->and($session->handleSessionFsReaddir(new SessionFsReaddirRequest('/', 'session-1')))->toBe(['entries' => ['x']])
            ->and($session->handleSessionFsReaddirWithTypes(new SessionFsReaddirWithTypesRequest('/', 'session-1')))
            ->toBe(['entries' => [['name' => 'x', 'type' => 'file']]])
            ->and($session->handleSessionFsRm(new SessionFsRmRequest('/a', 'session-1', force: false, recursive: true)))->toBe([])
            ->and($session->handleSessionFsRename(new SessionFsRenameRequest('/a', '/b', 'session-1')))->toBe([])
            ->and($calls)->toBe([
                ['appendFile', '/a', 'c', null],
                ['mkdir', '/d', true, null],
                ['rm', '/a', true, false],
                ['rename', '/a', '/b'],
            ]);
    });

    it('throws a JSON-RPC error for every callback when no provider is registered', function () {
        $session = sessionFsTestSession(null);

        foreach ([
            fn () => $session->handleSessionFsAppendFile(new SessionFsAppendFileRequest('x', '/a', 'session-1')),
            fn () => $session->handleSessionFsExists(new SessionFsExistsRequest('/a', 'session-1')),
            fn () => $session->handleSessionFsStat(new SessionFsStatRequest('/a', 'session-1')),
            fn () => $session->handleSessionFsMkdir(new SessionFsMkdirRequest('/a', 'session-1')),
            fn () => $session->handleSessionFsReaddir(new SessionFsReaddirRequest('/a', 'session-1')),
            fn () => $session->handleSessionFsReaddirWithTypes(new SessionFsReaddirWithTypesRequest('/a', 'session-1')),
            fn () => $session->handleSessionFsRm(new SessionFsRmRequest('/a', 'session-1')),
            fn () => $session->handleSessionFsRename(new SessionFsRenameRequest('/a', '/b', 'session-1')),
            fn () => $session->handleSessionFsSqliteQuery(new SessionFsSqliteQueryRequest('select 1', SessionFSSqliteQueryType::Query, 'session-1')),
            fn () => $session->handleSessionFsSqliteTransaction(new SessionFsSqliteTransactionRequest('session-1', [])),
            fn () => $session->handleSessionFsSqliteExists(new SessionFsSqliteExistsRequest('session-1')),
        ] as $call) {
            expect($call)->toThrow(JsonRpcException::class, 'No SessionFs provider configured');
        }
    });
});

describe('SessionFs SQLite callbacks', function () {
    it('runs queries and normalizes provider results', function () {
        $provider = new SessionFsTestProvider;
        $session = sessionFsTestSession($provider);

        $provider->sqlite->queryResult = new SessionFsSqliteQueryResult(['id'], [['id' => 1]], 0);
        $result = $session->handleSessionFsSqliteQuery(new SessionFsSqliteQueryRequest('select id', SessionFSSqliteQueryType::Query, 'session-1', ['a' => 1]));

        expect($result)->toBe(['columns' => ['id'], 'rows' => [['id' => 1]], 'rowsAffected' => 0])
            ->and($provider->calls[0])->toBe(['sqlite.query', [SessionFSSqliteQueryType::Query, 'select id', ['a' => 1]]]);

        $provider->sqlite->queryResult = ['columns' => [], 'rows' => [], 'rowsAffected' => 2, 'lastInsertRowid' => 7];
        expect($session->handleSessionFsSqliteQuery(new SessionFsSqliteQueryRequest('insert', SessionFSSqliteQueryType::Run, 'session-1')))
            ->toBe(['columns' => [], 'rows' => [], 'rowsAffected' => 2, 'lastInsertRowid' => 7.0]);

        $provider->sqlite->queryResult = null;
        expect($session->handleSessionFsSqliteQuery(new SessionFsSqliteQueryRequest('create', SessionFSSqliteQueryType::Exec, 'session-1')))
            ->toBe(['columns' => [], 'rows' => [], 'rowsAffected' => 0]);
    });

    it('lets query exceptions propagate as JSON-RPC errors', function () {
        $provider = new SessionFsTestProvider;
        $provider->failure = new RuntimeException('no such table');
        $session = sessionFsTestSession($provider);

        expect(fn () => $session->handleSessionFsSqliteQuery(new SessionFsSqliteQueryRequest('select', SessionFSSqliteQueryType::Query, 'session-1')))
            ->toThrow(RuntimeException::class, 'no such table');
    });

    it('checks whether the session database exists', function () {
        $session = sessionFsTestSession(new SessionFsTestProvider);

        expect($session->handleSessionFsSqliteExists(new SessionFsSqliteExistsRequest('session-1')))->toBe(['exists' => true]);
    });

    it('lets sqliteExists exceptions propagate', function () {
        $provider = new SessionFsTestProvider;
        $provider->failure = new RuntimeException('locked');
        $session = sessionFsTestSession($provider);

        expect(fn () => $session->handleSessionFsSqliteExists(new SessionFsSqliteExistsRequest('session-1')))
            ->toThrow(RuntimeException::class, 'locked');
    });

    it('runs transactions and passes typed statements', function () {
        $provider = new SessionFsTestProvider;
        $provider->sqlite->transactionResult = [
            new SessionFsSqliteQueryResult([], [], 1, lastInsertRowid: 3),
            ['columns' => ['n'], 'rows' => [['n' => 1]], 'rowsAffected' => 0],
        ];
        $session = sessionFsTestSession($provider);

        $result = $session->handleSessionFsSqliteTransaction(SessionFsSqliteTransactionRequest::fromArray([
            'sessionId' => 'session-1',
            'statements' => [
                ['query' => 'insert', 'queryType' => 'run', 'params' => ['a' => 1]],
                ['query' => 'select', 'queryType' => 'query'],
            ],
        ]));

        expect($result)->toBe(['results' => [
            ['columns' => [], 'rows' => [], 'rowsAffected' => 1, 'lastInsertRowid' => 3.0],
            ['columns' => ['n'], 'rows' => [['n' => 1]], 'rowsAffected' => 0],
        ]])
            ->and($provider->calls[0][1][0][0])->toBeInstanceOf(SessionFsSqliteTransactionStatement::class)
            ->and($provider->calls[0][1][0][0]->params)->toBe(['a' => 1])
            ->and($provider->calls[0][1][0][1]->queryType)->toBe(SessionFSSqliteQueryType::Query);
    });

    it('classifies transaction failures in the result instead of throwing', function () {
        $provider = new SessionFsTestProvider;
        $session = sessionFsTestSession($provider);
        $request = new SessionFsSqliteTransactionRequest('session-1', []);

        $provider->failure = new SessionFsSqliteTransactionFailure('database is locked', SessionFSSqliteTransactionErrorClass::BusyOrLocked);
        expect($session->handleSessionFsSqliteTransaction($request))
            ->toBe(['results' => [], 'error' => ['errorClass' => 'busyOrLocked', 'message' => 'database is locked']]);

        $provider->failure = new SessionFsSqliteTransactionFailure('commit unknown', SessionFSSqliteTransactionErrorClass::PostCommitAmbiguous);
        expect($session->handleSessionFsSqliteTransaction($request)['error']['errorClass'])->toBe('postCommitAmbiguous');

        $provider->failure = new SessionFsSqliteTransactionFailure('broken');
        expect($session->handleSessionFsSqliteTransaction($request)['error']['errorClass'])->toBe('fatal');

        $provider->failure = new RuntimeException('syntax error');
        expect($session->handleSessionFsSqliteTransaction($request))
            ->toBe(['results' => [], 'error' => ['errorClass' => 'fatal', 'message' => 'syntax error']]);
    });

    it('works with array providers', function () {
        $session = sessionFsTestSession([
            'readFile' => fn () => '',
            'writeFile' => fn () => null,
            'sqlite' => [
                'query' => fn (SessionFSSqliteQueryType $type, string $query) => ['columns' => [$type->value], 'rows' => [], 'rowsAffected' => 0],
                'exists' => fn () => false,
                'transaction' => fn (array $statements) => array_map(fn () => ['columns' => [], 'rows' => [], 'rowsAffected' => 1], $statements),
            ],
        ]);

        expect($session->handleSessionFsSqliteQuery(new SessionFsSqliteQueryRequest('q', SessionFSSqliteQueryType::Exec, 'session-1'))['columns'])->toBe(['exec'])
            ->and($session->handleSessionFsSqliteExists(new SessionFsSqliteExistsRequest('session-1')))->toBe(['exists' => false])
            ->and($session->handleSessionFsSqliteTransaction(SessionFsSqliteTransactionRequest::fromArray([
                'sessionId' => 'session-1',
                'statements' => [['query' => 'x', 'queryType' => 'run']],
            ]))['results'])->toHaveCount(1);
    });

    it('rejects SQLite calls on providers without SQLite support', function () {
        foreach ([sessionFsLegacyProvider(), ['readFile' => fn () => '', 'writeFile' => fn () => null]] as $provider) {
            $session = sessionFsTestSession($provider);

            expect(fn () => $session->handleSessionFsSqliteQuery(new SessionFsSqliteQueryRequest('q', SessionFSSqliteQueryType::Query, 'session-1')))
                ->toThrow(RuntimeException::class, 'SQLite is not supported by this provider')
                ->and(fn () => $session->handleSessionFsSqliteExists(new SessionFsSqliteExistsRequest('session-1')))
                ->toThrow(RuntimeException::class, 'SQLite is not supported by this provider')
                ->and($session->handleSessionFsSqliteTransaction(new SessionFsSqliteTransactionRequest('session-1', [])))
                ->toBe(['results' => [], 'error' => ['errorClass' => 'fatal', 'message' => 'SQLite transactions are not supported by this provider']]);
        }
    });

    it('reports unsupported transactions for SQLite providers without transaction support', function () {
        $sqlite = new class implements SessionFsSqliteProvider
        {
            public function query(SessionFSSqliteQueryType $queryType, string $query, ?array $params = null): SessionFsSqliteQueryResult|array|null
            {
                return null;
            }

            public function exists(): bool
            {
                return false;
            }
        };
        $session = sessionFsTestSession(new class($sqlite) implements SessionFsSqliteCapableProvider
        {
            public function __construct(private SessionFsSqliteProvider $sqlite) {}

            public function readFile(string $path): string
            {
                return '';
            }

            public function writeFile(string $path, string $content, ?int $mode = null): void {}

            public function sqlite(): SessionFsSqliteProvider
            {
                return $this->sqlite;
            }
        });

        expect($session->handleSessionFsSqliteTransaction(new SessionFsSqliteTransactionRequest('session-1', []))['error']['errorClass'])->toBe('fatal')
            ->and($session->handleSessionFsSqliteQuery(new SessionFsSqliteQueryRequest('q', SessionFSSqliteQueryType::Query, 'session-1')))
            ->toBe(['columns' => [], 'rows' => [], 'rowsAffected' => 0]);
    });
});

describe('SessionFs legacy provider compatibility', function () {
    it('keeps two-method providers working for reads and writes', function () {
        $session = sessionFsTestSession(sessionFsLegacyProvider());

        expect($session->handleSessionFsReadFile(new SessionFsReadFileRequest('/a', 'session-1')))
            ->toBe(['content' => 'legacy'])
            ->and($session->handleSessionFsWriteFile(new SessionFsWriteFileRequest('x', '/a', 'session-1')))
            ->toBe([]);
    });

    it('does not treat the two-method interface as binary, filesystem, or sqlite capable', function () {
        $legacy = sessionFsLegacyProvider();

        expect($legacy)->not->toBeInstanceOf(SessionFsBinaryProvider::class)
            ->and($legacy)->not->toBeInstanceOf(SessionFsFilesystemProvider::class)
            ->and($legacy)->not->toBeInstanceOf(SessionFsSqliteCapableProvider::class);
    });
});
