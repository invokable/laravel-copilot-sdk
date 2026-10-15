<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Catalog-reported token prices for an image model.
 */
readonly class ImageToolModelPricing implements Arrayable
{
    /**
     * @param  ?int  $batchSize  Number of tokens per billing batch
     * @param  ?float  $textInputPrice  AI credits per batch of text-input tokens
     * @param  ?float  $textOutputPrice  AI credits per batch of text-output tokens
     * @param  ?float  $imageInputPrice  AI credits per batch of image-input tokens
     * @param  ?float  $imageOutputPrice  AI credits per batch of image-output tokens
     */
    public function __construct(
        public ?int $batchSize = null,
        public ?float $textInputPrice = null,
        public ?float $textOutputPrice = null,
        public ?float $imageInputPrice = null,
        public ?float $imageOutputPrice = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            batchSize: isset($data['batchSize']) ? (int) $data['batchSize'] : null,
            textInputPrice: isset($data['textInputPrice']) ? (float) $data['textInputPrice'] : null,
            textOutputPrice: isset($data['textOutputPrice']) ? (float) $data['textOutputPrice'] : null,
            imageInputPrice: isset($data['imageInputPrice']) ? (float) $data['imageInputPrice'] : null,
            imageOutputPrice: isset($data['imageOutputPrice']) ? (float) $data['imageOutputPrice'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'batchSize' => $this->batchSize,
            'textInputPrice' => $this->textInputPrice,
            'textOutputPrice' => $this->textOutputPrice,
            'imageInputPrice' => $this->imageInputPrice,
            'imageOutputPrice' => $this->imageOutputPrice,
        ], fn ($value) => $value !== null);
    }
}
