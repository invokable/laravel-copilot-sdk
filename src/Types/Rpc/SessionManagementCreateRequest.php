<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\ContextTier;

/**
 * Requests a separate session. Omitted launch options inherit the caller's current configuration.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionManagementCreateRequest implements Arrayable
{
    public function __construct(
        public string $prompt,
        public ?string $name = null,
        public ?string $cwd = null,
        public ?string $model = null,
        public ?string $reasoningEffort = null,
        public ContextTier|string|null $contextTier = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $contextTier = $data['contextTier'] ?? null;
        if (is_string($contextTier)) {
            $contextTier = ContextTier::tryFrom($contextTier) ?? $contextTier;
        }

        return new self(
            prompt: $data['prompt'],
            name: $data['name'] ?? null,
            cwd: $data['cwd'] ?? null,
            model: $data['model'] ?? null,
            reasoningEffort: $data['reasoningEffort'] ?? null,
            contextTier: $contextTier,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'prompt' => $this->prompt,
            'name' => $this->name,
            'cwd' => $this->cwd,
            'model' => $this->model,
            'reasoningEffort' => $this->reasoningEffort,
            'contextTier' => $this->contextTier instanceof ContextTier ? $this->contextTier->value : $this->contextTier,
        ], fn ($value): bool => $value !== null);
    }
}
