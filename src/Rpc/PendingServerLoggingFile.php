<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\LoggingFileCloseRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileFlushRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileOpenRequest;
use Revolution\Copilot\Types\Rpc\LoggingFileOpenResult;
use Revolution\Copilot\Types\Rpc\LoggingFileWriteRequest;

/**
 * Experimental file logger operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerLoggingFile
{
    public function __construct(protected JsonRpcClient $client) {}

    public function open(LoggingFileOpenRequest|array $params = []): LoggingFileOpenResult
    {
        $paramsArray = ($params instanceof LoggingFileOpenRequest
            ? $params
            : LoggingFileOpenRequest::fromArray($params))->toArray();

        return LoggingFileOpenResult::fromArray(
            $this->client->request('logging.file.open', $paramsArray),
        );
    }

    public function write(LoggingFileWriteRequest|array $params): void
    {
        $paramsArray = ($params instanceof LoggingFileWriteRequest
            ? $params
            : LoggingFileWriteRequest::fromArray($params))->toArray();

        $this->client->request('logging.file.write', $paramsArray);
    }

    public function flush(LoggingFileFlushRequest|array $params): void
    {
        $paramsArray = ($params instanceof LoggingFileFlushRequest
            ? $params
            : LoggingFileFlushRequest::fromArray($params))->toArray();

        $this->client->request('logging.file.flush', $paramsArray);
    }

    public function close(LoggingFileCloseRequest|array $params): void
    {
        $paramsArray = ($params instanceof LoggingFileCloseRequest
            ? $params
            : LoggingFileCloseRequest::fromArray($params))->toArray();

        $this->client->request('logging.file.close', $paramsArray);
    }
}
