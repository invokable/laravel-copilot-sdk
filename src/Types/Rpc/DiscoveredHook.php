<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\HookOrigin;
use Revolution\Copilot\Enums\HookType;

/**
 * One server-discovered hook action.
 */
readonly class DiscoveredHook implements Arrayable
{
    public function __construct(
        public string $id,
        public HookType|string $hookType,
        public HookOrigin|string $origin,
        public string $source,
        public ?string $projectPath,
        public bool $enabled,
        public ?string $disableKey = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $hookType = Arr::string($data, 'hookType', '');
        $origin = Arr::string($data, 'origin', '');

        return new self(
            id: Arr::string($data, 'id', ''),
            hookType: HookType::tryFrom($hookType) ?? $hookType,
            origin: HookOrigin::tryFrom($origin) ?? $origin,
            source: Arr::string($data, 'source', ''),
            projectPath: isset($data['projectPath']) ? Arr::string($data, 'projectPath') : null,
            enabled: Arr::boolean($data, 'enabled', false),
            disableKey: isset($data['disableKey']) ? Arr::string($data, 'disableKey') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'hookType' => $this->hookType instanceof HookType ? $this->hookType->value : $this->hookType,
            'origin' => $this->origin instanceof HookOrigin ? $this->origin->value : $this->origin,
            'source' => $this->source,
            'projectPath' => $this->projectPath,
            'enabled' => $this->enabled,
            'disableKey' => $this->disableKey,
        ], fn ($value): bool => $value !== null);
    }
}
