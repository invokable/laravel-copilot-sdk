<?php

declare(strict_types=1);

namespace Revolution\Copilot\JsonRpc;

use JsonSerializable;

/** Explicit JSON-RPC null result; ordinary null handler results remain empty objects. */
final readonly class JsonRpcNullResult implements JsonSerializable
{
    public function jsonSerialize(): null
    {
        return null;
    }
}
