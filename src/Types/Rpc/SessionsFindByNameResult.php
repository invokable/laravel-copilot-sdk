<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Persisted session candidates matching a friendly name.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class SessionsFindByNameResult implements Arrayable
{
    /**
     * @param  array<SessionNameMatch>  $matches
     */
    public function __construct(
        public array $matches,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            matches: array_map(
                fn (array $match): SessionNameMatch => SessionNameMatch::fromArray($match),
                Arr::array($data, 'matches'),
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'matches' => array_map(
                fn (SessionNameMatch $match): array => $match->toArray(),
                $this->matches,
            ),
        ];
    }
}
