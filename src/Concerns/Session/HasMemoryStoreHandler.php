<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Closure;
use Illuminate\Support\Facades\Log;
use Revolution\Copilot\Exceptions\JsonRpcException;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\MemoryStoreCallOptions;
use Revolution\Copilot\Types\StoredMemory;
use RuntimeException;
use Throwable;

trait HasMemoryStoreHandler
{
    /**
     * @var Closure(StoredMemory, MemoryStoreCallOptions): void|null
     */
    protected ?Closure $memoryStoreHandler = null;

    /**
     * @internal
     */
    public function registerMemoryStoreHandler(?Closure $handler): void
    {
        $this->memoryStoreHandler = $handler;
    }

    /**
     * @internal
     */
    public function handleMemoryStore(StoredMemory $memory, CancellationToken $token): void
    {
        if ($this->memoryStoreHandler === null || $memory->sessionId !== $this->sessionId) {
            throw new RuntimeException('Memory store handler is unavailable');
        }

        try {
            ($this->memoryStoreHandler)($memory, new MemoryStoreCallOptions($token));
        } catch (Throwable $exception) {
            if ($token->isCancellationRequested()) {
                throw new JsonRpcException(-32800, 'Memory store call cancelled', previous: $exception);
            }

            Log::warning('Memory store callback failed', [
                'exception' => $exception,
                'sessionId' => $this->sessionId,
            ]);

            throw new RuntimeException('Memory store callback failed', previous: $exception);
        }

        if ($token->isCancellationRequested()) {
            throw new JsonRpcException(-32800, 'Memory store call cancelled');
        }
    }
}
