<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Cached startup model admission state. */
readonly class ModelStartupAdmissionResult implements Arrayable
{
    public function __construct(
        public ?string $selectedModel = null,
        public bool $catalogReady = false,
        public bool $requiresEnablement = false,
        public bool $claimedEnablement = false,
        public ?string $modelToEnable = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            selectedModel: $data['selectedModel'] ?? null,
            catalogReady: $data['catalogReady'] ?? false,
            requiresEnablement: $data['requiresEnablement'] ?? false,
            claimedEnablement: $data['claimedEnablement'] ?? false,
            modelToEnable: $data['modelToEnable'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'selectedModel' => $this->selectedModel,
            'catalogReady' => $this->catalogReady,
            'requiresEnablement' => $this->requiresEnablement,
            'claimedEnablement' => $this->claimedEnablement,
            'modelToEnable' => $this->modelToEnable,
        ], fn ($value) => $value !== null);
    }
}
