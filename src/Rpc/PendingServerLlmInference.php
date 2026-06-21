<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseChunkRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseChunkResult;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseStartRequest;
use Revolution\Copilot\Types\Rpc\LlmInferenceHTTPResponseStartResult;
use Revolution\Copilot\Types\Rpc\LlmInferenceSetProviderResult;

/**
 * Server-scoped LLM inference RPC operations.
 *
 * Allows an SDK client to register as the LLM inference callback provider
 * and deliver HTTP response frames for in-flight model-layer requests.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerLlmInference
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Registers this SDK client as the LLM inference callback provider.
     */
    public function setProvider(): LlmInferenceSetProviderResult
    {
        return LlmInferenceSetProviderResult::fromArray(
            $this->client->request('llmInference.setProvider', []),
        );
    }

    /**
     * Delivers the response head (status + headers) for an in-flight request.
     *
     * Must be called exactly once per request before any httpResponseChunk frames.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function httpResponseStart(LlmInferenceHTTPResponseStartRequest|array $params): LlmInferenceHTTPResponseStartResult
    {
        $paramsArray = ($params instanceof LlmInferenceHTTPResponseStartRequest ? $params : LlmInferenceHTTPResponseStartRequest::fromArray($params))->toArray();

        return LlmInferenceHTTPResponseStartResult::fromArray(
            $this->client->request('llmInference.httpResponseStart', $paramsArray),
        );
    }

    /**
     * Delivers a body byte range (or a terminal transport error) for an in-flight response.
     *
     * Set `end` true on the last chunk.
     *
     * @experimental This API group is experimental and may change or be removed.
     */
    public function httpResponseChunk(LlmInferenceHTTPResponseChunkRequest|array $params): LlmInferenceHTTPResponseChunkResult
    {
        $paramsArray = ($params instanceof LlmInferenceHTTPResponseChunkRequest ? $params : LlmInferenceHTTPResponseChunkRequest::fromArray($params))->toArray();

        return LlmInferenceHTTPResponseChunkResult::fromArray(
            $this->client->request('llmInference.httpResponseChunk', $paramsArray),
        );
    }
}
