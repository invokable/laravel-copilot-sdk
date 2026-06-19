<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Long context tier pricing (available for models with extended context windows).
 */
readonly class ModelBillingTokenPricesLongContext implements Arrayable
{
    /**
     * @param  ?float  $inputPrice  AI Credits cost per billing batch of input tokens
     * @param  ?float  $outputPrice  AI Credits cost per billing batch of output tokens
     * @param  ?float  $cachePrice  AI Credits cost per billing batch of cached tokens
     * @param  ?int  $contextMax  Prompt token budget for the long context tier
     */
    public function __construct(
        public ?float $inputPrice = null,
        public ?float $outputPrice = null,
        public ?float $cachePrice = null,
        public ?int $contextMax = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            inputPrice: isset($data['inputPrice']) ? (float) $data['inputPrice'] : null,
            outputPrice: isset($data['outputPrice']) ? (float) $data['outputPrice'] : null,
            cachePrice: isset($data['cachePrice']) ? (float) $data['cachePrice'] : null,
            contextMax: isset($data['contextMax']) ? (int) $data['contextMax'] : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'inputPrice' => $this->inputPrice,
            'outputPrice' => $this->outputPrice,
            'cachePrice' => $this->cachePrice,
            'contextMax' => $this->contextMax,
        ], fn ($v) => $v !== null);
    }
}
