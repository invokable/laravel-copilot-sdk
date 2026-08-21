<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanEnumValueType;
use Revolution\Copilot\Enums\McpPlanRequiredValueEnumKind;
use Revolution\Copilot\Enums\McpPlanValueCategory;

/**
 * One enumerated non-secret value a transport choice needs.
 *
 * @experimental
 */
readonly class McpPlanRequiredValueEnum implements Arrayable
{
    /**
     * @param  string[]  $enumValues  Non-empty permitted value set.
     */
    public function __construct(
        public McpPlanRequiredValueEnumKind $kind,
        public string $key,
        public McpPlanValueCategory $category,
        public McpPlanEnumValueType $valueType,
        public bool $required,
        public bool $isRepeated,
        public array $enumValues,
        public ?string $defaultValue = null,
        public ?string $title = null,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: McpPlanRequiredValueEnumKind::from($data['kind']),
            key: $data['key'],
            category: McpPlanValueCategory::from($data['category']),
            valueType: McpPlanEnumValueType::from($data['valueType']),
            required: (bool) ($data['required'] ?? false),
            isRepeated: (bool) ($data['isRepeated'] ?? false),
            enumValues: $data['enumValues'] ?? [],
            defaultValue: $data['defaultValue'] ?? null,
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        $arr = [
            'kind' => $this->kind->value,
            'key' => $this->key,
            'category' => $this->category->value,
            'valueType' => $this->valueType->value,
            'required' => $this->required,
            'isRepeated' => $this->isRepeated,
            'enumValues' => $this->enumValues,
        ];
        if ($this->defaultValue !== null) {
            $arr['defaultValue'] = $this->defaultValue;
        }
        if ($this->title !== null) {
            $arr['title'] = $this->title;
        }
        if ($this->description !== null) {
            $arr['description'] = $this->description;
        }

        return $arr;
    }
}
