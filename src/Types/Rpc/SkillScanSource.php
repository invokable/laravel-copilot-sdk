<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\LocalSkillDefinitionSource;

/**
 * @experimental
 */
readonly class SkillScanSource implements Arrayable
{
    public function __construct(
        public string $path,
        public LocalSkillDefinitionSource|string $source,
        public ?string $pluginId = null,
        public ?string $pluginName = null,
        public ?string $pluginVersion = null,
        public ?string $tier = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $source = Arr::string($data, 'source');

        return new self(
            path: Arr::string($data, 'path'),
            source: LocalSkillDefinitionSource::tryFrom($source) ?? $source,
            pluginId: $data['pluginId'] ?? null,
            pluginName: $data['pluginName'] ?? null,
            pluginVersion: $data['pluginVersion'] ?? null,
            tier: $data['tier'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'path' => $this->path,
            'source' => $this->source instanceof LocalSkillDefinitionSource ? $this->source->value : $this->source,
            'pluginId' => $this->pluginId,
            'pluginName' => $this->pluginName,
            'pluginVersion' => $this->pluginVersion,
            'tier' => $this->tier,
        ], fn ($value) => $value !== null);
    }
}
