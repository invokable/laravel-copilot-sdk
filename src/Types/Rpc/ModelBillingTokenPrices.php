<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Token-level pricing information for a model.
 */
readonly class ModelBillingTokenPrices implements Arrayable
{
    /**
     * @param  ?int  $inputPrice  AI Credits cost per billing batch of input tokens
     * @param  ?int  $outputPrice  AI Credits cost per billing batch of output tokens
     * @param  ?int  $cachePrice  AI Credits cost per billing batch of cached tokens
     * @param  ?int  $batchSize  Number of tokens per standard billing batch
     * @param  ?int  $contextMax  Prompt token budget for the default tier
     * @param  ModelBillingTokenPricesLongContext|null  $longContext  Long context tier pricing
     */
    public function __construct(
        public ?int $inputPrice = null,
        public ?int $outputPrice = null,
        public ?int $cachePrice = null,
        public ?int $batchSize = null,
        public ?int $contextMax = null,
        public ?ModelBillingTokenPricesLongContext $longContext = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            inputPrice: isset($data['inputPrice']) ? (int) $data['inputPrice'] : null,
            outputPrice: isset($data['outputPrice']) ? (int) $data['outputPrice'] : null,
            cachePrice: isset($data['cachePrice']) ? (int) $data['cachePrice'] : null,
            batchSize: isset($data['batchSize']) ? (int) $data['batchSize'] : null,
            contextMax: isset($data['contextMax']) ? (int) $data['contextMax'] : null,
            longContext: isset($data['longContext']) ? ModelBillingTokenPricesLongContext::fromArray($data['longContext']) : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'inputPrice' => $this->inputPrice,
            'outputPrice' => $this->outputPrice,
            'cachePrice' => $this->cachePrice,
            'batchSize' => $this->batchSize,
            'contextMax' => $this->contextMax,
            'longContext' => $this->longContext?->toArray(),
        ], fn ($v) => $v !== null);
    }
}
