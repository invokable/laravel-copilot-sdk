<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\FileLogLevel;
use Revolution\Copilot\Enums\FileLogOperation;
use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Rpc\PendingServerLogging;
use Revolution\Copilot\Rpc\PendingServerLoggingFile;
use Revolution\Copilot\Rpc\ServerRpc;
use Revolution\Copilot\Types\Rpc\LoggingFileCloseRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileFlushRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileOpenRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileOpenResult;
use Revolution\Copilot\Types\Rpc\LoggingFileWriteRequest;

describe('PendingServerLogging', function () {
    it('exposes file logger operations from the server RPC', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('logging.file.open', [
                'level' => 'info',
                'debugEnvironmentVariables' => ['APP_DEBUG'],
                'secretValues' => ['sensitive-value'],
            ])
            ->andReturn(['logId' => 'file-log-1']);
        $client->shouldReceive('request')
            ->once()
            ->with('logging.file.write', [
                'logId' => 'file-log-1',
                'operation' => 'warning',
                'message' => 'A warning',
                'groupLevel' => 'debug',
            ])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('logging.file.flush', ['logId' => 'file-log-1'])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('logging.file.close', ['logId' => 'file-log-1'])
            ->andReturn([]);

        $logging = (new ServerRpc($client))->logging();
        $file = $logging->file();
        $opened = $file->open(new LoggingFileOpenRequest(
            level: FileLogLevel::Info,
            debugEnvironmentVariables: ['APP_DEBUG'],
            secretValues: ['sensitive-value'],
        ));
        $file->write(new LoggingFileWriteRequest(
            logId: $opened->logId,
            operation: FileLogOperation::Warning,
            message: 'A warning',
            groupLevel: FileLogLevel::Debug,
        ));
        $file->flush(['logId' => $opened->logId]);
        $file->close(new LoggingFileCloseRequest($opened->logId));

        expect($logging)->toBeInstanceOf(PendingServerLogging::class)
            ->and($file)->toBeInstanceOf(PendingServerLoggingFile::class)
            ->and($opened)->toBeInstanceOf(LoggingFileOpenResult::class)
            ->and($opened->logId)->toBe('file-log-1');
    });

    it('accepts array requests and dispatches each file logging method', function () {
        $client = Mockery::mock(JsonRpcClient::class);
        $client->shouldReceive('request')
            ->once()
            ->with('logging.file.open', [])
            ->andReturn(['logId' => 'file-log-2']);
        $client->shouldReceive('request')
            ->once()
            ->with('logging.file.write', [
                'logId' => 'file-log-2',
                'operation' => 'log',
                'message' => 'Ready',
            ])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('logging.file.flush', ['logId' => 'file-log-2'])
            ->andReturn([]);
        $client->shouldReceive('request')
            ->once()
            ->with('logging.file.close', ['logId' => 'file-log-2'])
            ->andReturn([]);

        $file = (new ServerRpc($client))->logging()->file();
        $opened = $file->open();
        $file->write([
            'logId' => $opened->logId,
            'operation' => 'log',
            'message' => 'Ready',
        ]);
        $file->flush(new LoggingFileFlushRequest($opened->logId));
        $file->close(['logId' => $opened->logId]);

        expect($opened->logId)->toBe('file-log-2');
    });
});
