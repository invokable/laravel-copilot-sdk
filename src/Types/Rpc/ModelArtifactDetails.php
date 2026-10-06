<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class ModelArtifactDetails implements Arrayable
{
    /**
     * @param  string[]|null  $families
     */
    public function __construct(
        public ?string $architecture = null,
        public ?array $families = null,
        public ?string $family = null,
        public ?string $format = null,
        public ?string $parameterSize = null,
        public ?string $quantization = null,
        public ?string $tokenizer = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            architecture: $data['architecture'] ?? null,
            families: $data['families'] ?? null,
            family: $data['family'] ?? null,
            format: $data['format'] ?? null,
            parameterSize: $data['parameterSize'] ?? null,
            quantization: $data['quantization'] ?? null,
            tokenizer: $data['tokenizer'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'architecture' => $this->architecture,
            'families' => $this->families,
            'family' => $this->family,
            'format' => $this->format,
            'parameterSize' => $this->parameterSize,
            'quantization' => $this->quantization,
            'tokenizer' => $this->tokenizer,
        ], static fn ($value) => $value !== null);
    }
}
