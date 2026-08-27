<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\ReasoningEffort;
use Revolution\Copilot\Types\Rpc\ModelMessage;

/**
 * Information about an available model.
 */
readonly class ModelInfo implements Arrayable
{
    /**
     * @param  string  $id  Model identifier (e.g., "claude-sonnet-4.5")
     * @param  string  $name  Display name
     * @param  ModelCapabilities  $capabilities  Model capabilities and limits
     * @param  ?ModelPolicy  $policy  Policy state
     * @param  ?ModelBilling  $billing  Billing information
     * @param  ?array  $supportedReasoningEfforts  Supported reasoning effort levels (only present if model supports reasoning effort)
     * @param  ReasoningEffort|string|null  $defaultReasoningEffort  Default reasoning effort level (only present if model supports reasoning effort)
     * @param  ?ModelWarningText  $warningText  Service-published warning text.
     * @param  ?array  $infoMessages  Service-published informational messages.
     * @param  ?array  $warningMessages  Service-published warning messages.
     */
    public function __construct(
        public string $id,
        public string $name,
        public ModelCapabilities $capabilities,
        public ?ModelPolicy $policy = null,
        public ?ModelBilling $billing = null,
        public ?array $supportedReasoningEfforts = null,
        public ReasoningEffort|string|null $defaultReasoningEffort = null,
        public ?ModelWarningText $warningText = null,
        public ?array $infoMessages = null,
        public ?array $warningMessages = null,
    ) {}

    /**
     * Create from array.
     *
     * @param  array{id: string, name: string, capabilities: array, policy?: array, billing?: array, supportedReasoningEfforts?: array, defaultReasoningEffort?: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: Arr::string($data, 'id'),
            name: Arr::string($data, 'name'),
            capabilities: ModelCapabilities::fromArray($data['capabilities']),
            policy: isset($data['policy']) ? ModelPolicy::fromArray($data['policy']) : null,
            billing: isset($data['billing']) ? ModelBilling::fromArray($data['billing']) : null,
            supportedReasoningEfforts: $data['supportedReasoningEfforts'] ?? null,
            defaultReasoningEffort: $data['defaultReasoningEffort'] ?? null,
            warningText: isset($data['warningText']) ? ModelWarningText::fromArray($data['warningText']) : null,
            infoMessages: isset($data['infoMessages'])
                ? array_map(fn (array $message) => ModelMessage::fromArray($message), $data['infoMessages'])
                : null,
            warningMessages: isset($data['warningMessages'])
                ? array_map(fn (array $message) => ModelMessage::fromArray($message), $data['warningMessages'])
                : null,
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        $defaultReasoningEffort = $this->defaultReasoningEffort instanceof ReasoningEffort
            ? $this->defaultReasoningEffort->value
            : $this->defaultReasoningEffort;

        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'capabilities' => $this->capabilities->toArray(),
            'policy' => $this->policy?->toArray(),
            'billing' => $this->billing?->toArray(),
            'supportedReasoningEfforts' => $this->supportedReasoningEfforts,
            'defaultReasoningEffort' => $defaultReasoningEffort,
            'warningText' => $this->warningText?->toArray(),
            'infoMessages' => $this->infoMessages === null
                ? null
                : array_map(fn ($message) => $message instanceof ModelMessage ? $message->toArray() : $message, $this->infoMessages),
            'warningMessages' => $this->warningMessages === null
                ? null
                : array_map(fn ($message) => $message instanceof ModelMessage ? $message->toArray() : $message, $this->warningMessages),
        ], fn ($v) => $v !== null);
    }
}
