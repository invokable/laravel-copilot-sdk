<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanRemoteInstallMethod;
use Revolution\Copilot\Enums\McpPlanRemoteTransport;

/**
 * An eligible remote-endpoint transport choice.
 *
 * @experimental
 */
readonly class McpPlanTransportChoiceRemote implements Arrayable
{
    /**
     * @param  McpPlanRequiredValueScalar[]|McpPlanRequiredValueEnum[]  $requiredValues
     * @param  McpPlanSecretPlaceholder[]  $secretPlaceholders
     */
    public function __construct(
        public string $choiceId,
        public McpPlanRemoteTransport $transport,
        public McpPlanRemoteInstallMethod $installMethod,
        public string $endpoint,
        public array $requiredValues,
        public array $secretPlaceholders,
    ) {}

    public static function fromArray(array $data): static
    {
        $requiredValues = array_map(
            fn (array $v) => ($v['kind'] ?? '') === 'enum'
                ? McpPlanRequiredValueEnum::fromArray($v)
                : McpPlanRequiredValueScalar::fromArray($v),
            $data['requiredValues'] ?? [],
        );

        return new static(
            choiceId: $data['choiceId'],
            transport: McpPlanRemoteTransport::from($data['transport']),
            installMethod: McpPlanRemoteInstallMethod::from($data['installMethod']),
            endpoint: $data['endpoint'],
            requiredValues: $requiredValues,
            secretPlaceholders: array_map(
                fn (array $p) => McpPlanSecretPlaceholder::fromArray($p),
                $data['secretPlaceholders'] ?? [],
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'choiceId' => $this->choiceId,
            'transport' => $this->transport->value,
            'installMethod' => $this->installMethod->value,
            'endpoint' => $this->endpoint,
            'requiredValues' => array_map(fn ($v) => $v->toArray(), $this->requiredValues),
            'secretPlaceholders' => array_map(fn ($p) => $p->toArray(), $this->secretPlaceholders),
        ];
    }
}
