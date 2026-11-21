<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;

/**
 * Experimental runtime logging operations.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerLogging
{
    public function __construct(protected JsonRpcClient $client) {}

    /**
     * File logger operations.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function file(): PendingServerLoggingFile
    {
        return new PendingServerLoggingFile($this->client);
    }
}
