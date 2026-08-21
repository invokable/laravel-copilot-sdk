<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\McpPlanRequiredValueScalarKind;
use Revolution\Copilot\Enums\McpPlanScalarValueType;
use Revolution\Copilot\Enums\McpPlanValueCategory;

/**
 * One non-secret scalar value a transport choice needs.
 *
 * @experimental
 */
readonly class McpPlanRequiredValueScalar implements Arrayable
{
    public function __construct(
        public McpPlanRequiredValueScalarKind $kind,
        public string $key,
        public McpPlanValueCategory $category,
        public McpPlanScalarValueType $valueType,
        public bool $required,
        public bool $isRepeated,
        public ?string $defaultValue = null,
        public ?string $title = null,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            kind: McpPlanRequiredValueScalarKind::from($data['kind']),
            key: $data['key'],
            category: McpPlanValueCategory::from($data['category']),
            valueType: McpPlanScalarValueType::from($data['valueType']),
            required: (bool) ($data['required'] ?? false),
            isRepeated: (bool) ($data['isRepeated'] ?? false),
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
