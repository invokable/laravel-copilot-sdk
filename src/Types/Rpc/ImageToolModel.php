<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\ImageToolModelAvailability;
use Revolution\Copilot\Enums\ImageToolModelOperation;

/**
 * Runtime-selected image model and its account availability.
 */
readonly class ImageToolModel implements Arrayable
{
    /**
     * @param  array<ImageToolModelOperation|string>  $supportedOperations  Image operations supported by the model
     * @param  ImageToolModelAvailability|string  $availability  Whether the resolved account can use this model
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $supportedOperations,
        public bool $isDefault,
        public ImageToolModelAvailability|string $availability,
        public ?ImageToolModelPricing $pricing = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $availability = Arr::string($data, 'availability');

        return new self(
            id: Arr::string($data, 'id'),
            name: Arr::string($data, 'name'),
            supportedOperations: array_map(
                fn (string $operation) => ImageToolModelOperation::tryFrom($operation) ?? $operation,
                $data['supportedOperations'] ?? [],
            ),
            isDefault: (bool) ($data['isDefault'] ?? false),
            availability: ImageToolModelAvailability::tryFrom($availability) ?? $availability,
            pricing: isset($data['pricing'])
                ? ($data['pricing'] instanceof ImageToolModelPricing
                    ? $data['pricing']
                    : ImageToolModelPricing::fromArray($data['pricing']))
                : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'supportedOperations' => array_map(
                fn (ImageToolModelOperation|string $operation) => $operation instanceof ImageToolModelOperation
                    ? $operation->value
                    : $operation,
                $this->supportedOperations,
            ),
            'isDefault' => $this->isDefault,
            'availability' => $this->availability instanceof ImageToolModelAvailability
                ? $this->availability->value
                : $this->availability,
            'pricing' => $this->pricing?->toArray(),
        ], fn ($value) => $value !== null);
    }
}
