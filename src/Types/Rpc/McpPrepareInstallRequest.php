<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpInstallationSecretStorage;

/**
 * Prepares a selected MCP install plan for confirmation and application.
 *
 * @experimental
 */
readonly class McpPrepareInstallRequest implements Arrayable
{
    /**
     * @param  list<McpInstallationInput|array<string, mixed>>  $inputs
     * @param  list<McpInstallationSecret|array<string, mixed>>  $secrets
     */
    public function __construct(
        public CatalogClientContract $contract,
        public string $planHandle,
        public string $choiceId,
        public array $inputs,
        public string $policySessionId,
        public array $secrets,
        public McpInstallationSecretStorage $secretStorage,
        public McpServerCardReference $source,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            contract: CatalogClientContract::fromArray($data['contract']),
            planHandle: $data['planHandle'],
            choiceId: $data['choiceId'],
            inputs: array_map(static fn (array $input): McpInstallationInput => McpInstallationInput::fromArray($input), $data['inputs']),
            policySessionId: $data['policySessionId'],
            secrets: array_map(static fn (array $secret): McpInstallationSecret => McpInstallationSecret::fromArray($secret), $data['secrets']),
            secretStorage: McpInstallationSecretStorage::from($data['secretStorage']),
            source: McpServerCardReference::fromArray($data['source']),
        );
    }

    public function toArray(): array
    {
        return [
            'contract' => $this->contract->toArray(),
            'planHandle' => $this->planHandle,
            'choiceId' => $this->choiceId,
            'policySessionId' => $this->policySessionId,
            'inputs' => array_map(
                static fn (McpInstallationInput|array $input): array => $input instanceof McpInstallationInput
                    ? $input->toArray()
                    : McpInstallationInput::fromArray($input)->toArray(),
                $this->inputs,
            ),
            'secrets' => array_map(
                static fn (McpInstallationSecret|array $secret): array => $secret instanceof McpInstallationSecret
                    ? $secret->toArray()
                    : McpInstallationSecret::fromArray($secret)->toArray(),
                $this->secrets,
            ),
            'secretStorage' => $this->secretStorage->value,
            'source' => $this->source->toArray(),
        ];
    }
}
