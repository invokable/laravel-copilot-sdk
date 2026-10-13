<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Per-statement results, or a classified transaction error.
 */
readonly class SessionFsSqliteTransactionResult implements Arrayable
{
    /**
     * @param  list<SessionFsSqliteQueryResult>  $results  Per-statement query results in input order
     * @param  ?SessionFsSqliteTransactionError  $error  Classified transaction failure
     */
    public function __construct(
        public array $results,
        public ?SessionFsSqliteTransactionError $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            results: array_values(array_map(
                fn (SessionFsSqliteQueryResult|array $result) => $result instanceof SessionFsSqliteQueryResult
                    ? $result
                    : SessionFsSqliteQueryResult::fromArray($result),
                $data['results'] ?? [],
            )),
            error: isset($data['error']) ? SessionFsSqliteTransactionError::fromArray($data['error']) : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'results' => array_map(
                fn (SessionFsSqliteQueryResult $item) => $item->toArray(),
                $this->results,
            ),
        ];

        if ($this->error !== null) {
            $result['error'] = $this->error->toArray();
        }

        return $result;
    }
}
