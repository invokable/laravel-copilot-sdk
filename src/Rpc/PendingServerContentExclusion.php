<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\ContentExclusionValidatePoliciesRequest;
use Revolution\Copilot\Types\Rpc\ContentExclusionValidatePoliciesResult;

/**
 * Server-scoped content-exclusion policy RPC operations.
 */
class PendingServerContentExclusion
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Validate raw content-exclusion policy JSON without creating a session.
     */
    public function validatePolicies(ContentExclusionValidatePoliciesRequest|array $params): ContentExclusionValidatePoliciesResult
    {
        $request = $params instanceof ContentExclusionValidatePoliciesRequest
            ? $params
            : ContentExclusionValidatePoliciesRequest::fromArray($params);

        return ContentExclusionValidatePoliciesResult::fromArray(
            $this->client->request('contentExclusion.validatePolicies', $request->toArray()),
        );
    }
}
