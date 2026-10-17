<?php

declare(strict_types=1);

namespace Revolution\Copilot\Rpc;

use Revolution\Copilot\JsonRpc\JsonRpcClient;
use Revolution\Copilot\Types\Rpc\CatalogGetGeneratedFeedRequest;
use Revolution\Copilot\Types\Rpc\CatalogGetSavedFeedRequest;
use Revolution\Copilot\Types\Rpc\CatalogPlanInstallFromReferenceRequest;
use Revolution\Copilot\Types\Rpc\CatalogReferenceInstallationResult;
use Revolution\Copilot\Types\Rpc\CatalogSearchRequest;
use Revolution\Copilot\Types\Rpc\CatalogSelectionRequest;

/**
 * Pending catalog RPC operations (server-scoped).
 *
 * The catalog surface is available through SDK/TUI hosts; standalone runtimes
 * whose host does not implement server-method dispatch return JSON-RPC
 * MethodNotFound. All returned text, URLs, and package metadata are untrusted
 * external data and can never trigger instructions, tools, or installation.
 * Read-only: nothing is installed, configured, or persisted.
 *
 * @experimental This API group is experimental and may change or be removed.
 */
class PendingServerCatalog
{
    public function __construct(
        protected JsonRpcClient $client,
    ) {}

    /**
     * Requests a bounded catalog search.
     *
     * A runtime with search available returns inert candidate summaries, each
     * with an opaque single-use handle scoped to this runtime instance; a
     * runtime without it returns the typed search-unavailable result. Public
     * authorities may be searched anonymously, while an authority that requires
     * credentials yields the typed authentication-required result.
     * Supplying categories requires the catalog-search-categories capability.
     * Supplying policySessionId requires catalog-search-credential-required and
     * catalog-search-session-bound, and only binds to an existing attached session.
     *
     * Returns an associative array whose `kind` key identifies the result
     * variant (`succeeded`, `negotiation-refused`, `invalid-request`, etc.).
     *
     * @experimental
     */
    public function search(CatalogSearchRequest|array $params): array
    {
        $paramsArray = ($params instanceof CatalogSearchRequest
            ? $params
            : CatalogSearchRequest::fromArray($params))->toArray();

        return $this->client->request('catalog.search', $paramsArray);
    }

    /**
     * Retrieves one saved feed with its entries in feed order.
     *
     * The contract must require catalog-feeds and
     * catalog-search-credential-required. When policySessionId is supplied, it
     * must also require catalog-search-session-bound. The runtime binds only to
     * an existing attached session and does not retry anonymously.
     *
     * @return array<string, mixed>
     *
     * @experimental
     */
    public function getSavedFeed(CatalogGetSavedFeedRequest|array $params): array
    {
        $paramsArray = ($params instanceof CatalogGetSavedFeedRequest
            ? $params
            : CatalogGetSavedFeedRequest::fromArray($params))->toArray();

        return $this->client->request('catalog.getSavedFeed', $paramsArray);
    }

    /**
     * Retrieves one generated feed with its entries in feed order.
     *
     * The contract must require catalog-feeds and
     * catalog-search-credential-required. When policySessionId is supplied, it
     * must also require catalog-search-session-bound. The runtime binds only to
     * an existing attached session and does not retry anonymously.
     *
     * @return array<string, mixed>
     *
     * @experimental
     */
    public function getGeneratedFeed(CatalogGetGeneratedFeedRequest|array $params): array
    {
        $paramsArray = ($params instanceof CatalogGetGeneratedFeedRequest
            ? $params
            : CatalogGetGeneratedFeedRequest::fromArray($params))->toArray();

        return $this->client->request('catalog.getGeneratedFeed', $paramsArray);
    }

    /**
     * Resolves a candidate from a previous search for this session.
     *
     * On a `selected` result, pass its fresh `candidateHandle` to the matching
     * skill or plugin `planInstall` operation.
     *
     * @return array<string, mixed>
     *
     * @experimental
     */
    public function select(CatalogSelectionRequest|array $params): array
    {
        $paramsArray = ($params instanceof CatalogSelectionRequest
            ? $params
            : CatalogSelectionRequest::fromArray($params))->toArray();

        return $this->client->request('catalog.select', $paramsArray);
    }

    /**
     * Plans installation of the resource pinned by a verified materialisation reference.
     *
     * The outcome kind identifies whether the reference describes a skill or an Agent Plugin.
     *
     * @experimental
     */
    public function planInstallFromReference(CatalogPlanInstallFromReferenceRequest|array $params): CatalogReferenceInstallationResult
    {
        $paramsArray = ($params instanceof CatalogPlanInstallFromReferenceRequest
            ? $params
            : CatalogPlanInstallFromReferenceRequest::fromArray($params))->toArray();

        return CatalogReferenceInstallationResult::fromArray(
            $this->client->request('catalog.planInstallFromReference', $paramsArray),
        );
    }
}
