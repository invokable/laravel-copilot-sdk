<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * A normalised, inert description of what installing an MCP server would involve.
 *
 * @experimental
 */
readonly class McpInstallPlan implements Arrayable
{
    /**
     * @param  McpPlanTransportChoicePackage[]|McpPlanTransportChoiceRemote[]  $transportChoices
     * @param  McpPlanConfigurationChange[]  $configurationChanges
     */
    public function __construct(
        public string $planHandle,
        public string $planHandleExpiresAt,
        public McpPlanResourceIdentity $identity,
        public McpPlanProvenance $provenance,
        public array $transportChoices,
        public McpPlanTarget $target,
        public McpPlanPolicyResult $policy,
        public array $configurationChanges,
        public bool $reloadRequired,
        public bool $requiresInteractiveConfiguration,
        public ?string $recommendedTransportChoiceId = null,
    ) {}

    public static function fromArray(array $data): static
    {
        $transportChoices = array_map(
            fn (array $c) => ($c['installMethod'] ?? '') === 'package'
                ? McpPlanTransportChoicePackage::fromArray($c)
                : McpPlanTransportChoiceRemote::fromArray($c),
            $data['transportChoices'] ?? [],
        );

        return new static(
            planHandle: $data['planHandle'],
            planHandleExpiresAt: $data['planHandleExpiresAt'],
            identity: McpPlanResourceIdentity::fromArray($data['identity']),
            provenance: McpPlanProvenance::fromArray($data['provenance']),
            transportChoices: $transportChoices,
            target: McpPlanTarget::fromArray($data['target']),
            policy: McpPlanPolicyResult::fromArray($data['policy']),
            configurationChanges: array_map(
                fn (array $c) => McpPlanConfigurationChange::fromArray($c),
                $data['configurationChanges'] ?? [],
            ),
            reloadRequired: (bool) ($data['reloadRequired'] ?? false),
            requiresInteractiveConfiguration: (bool) ($data['requiresInteractiveConfiguration'] ?? false),
            recommendedTransportChoiceId: $data['recommendedTransportChoiceId'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'planHandle' => $this->planHandle,
            'planHandleExpiresAt' => $this->planHandleExpiresAt,
            'identity' => $this->identity->toArray(),
            'provenance' => $this->provenance->toArray(),
            'transportChoices' => array_map(fn ($c) => $c->toArray(), $this->transportChoices),
            'target' => $this->target->toArray(),
            'policy' => $this->policy->toArray(),
            'configurationChanges' => array_map(fn ($c) => $c->toArray(), $this->configurationChanges),
            'reloadRequired' => $this->reloadRequired,
            'requiresInteractiveConfiguration' => $this->requiresInteractiveConfiguration,
        ];
        if ($this->recommendedTransportChoiceId !== null) {
            $arr['recommendedTransportChoiceId'] = $this->recommendedTransportChoiceId;
        }

        return $arr;
    }
}
