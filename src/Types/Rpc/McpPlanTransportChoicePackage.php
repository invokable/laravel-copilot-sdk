<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanPackageInstallMethod;
use Revolution\Copilot\Enums\McpPlanPackageTransport;

/**
 * An eligible local-package transport choice.
 *
 * @experimental
 */
readonly class McpPlanTransportChoicePackage implements Arrayable
{
    /**
     * @param  McpPlanRequiredValueScalar[]|McpPlanRequiredValueEnum[]  $requiredValues
     * @param  McpPlanSecretPlaceholder[]  $secretPlaceholders
     */
    public function __construct(
        public string $choiceId,
        public McpPlanPackageTransport $transport,
        public McpPlanPackageInstallMethod $installMethod,
        public string $packageType,
        public string $packageIdentifier,
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
            transport: McpPlanPackageTransport::from($data['transport']),
            installMethod: McpPlanPackageInstallMethod::from($data['installMethod']),
            packageType: $data['packageType'],
            packageIdentifier: $data['packageIdentifier'],
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
            'packageType' => $this->packageType,
            'packageIdentifier' => $this->packageIdentifier,
            'requiredValues' => array_map(fn ($v) => $v->toArray(), $this->requiredValues),
            'secretPlaceholders' => array_map(fn ($p) => $p->toArray(), $this->secretPlaceholders),
        ];
    }
}
