<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\SessionFSSqliteQueryType;

/**
 * One statement in an atomic SQLite transaction.
 */
readonly class SessionFsSqliteTransactionStatement implements Arrayable
{
    /**
     * @param  string  $query  SQL statement to execute
     * @param  SessionFSSqliteQueryType  $queryType  How to execute the statement
     * @param  array<string, float|int|string|null>|null  $params  Optional named bind parameters
     */
    public function __construct(
        public string $query,
        public SessionFSSqliteQueryType $queryType,
        public ?array $params = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            query: Arr::string($data, 'query', ''),
            queryType: SessionFSSqliteQueryType::from($data['queryType']),
            params: isset($data['params']) ? (array) $data['params'] : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'query' => $this->query,
            'queryType' => $this->queryType->value,
        ];

        if ($this->params !== null) {
            $result['params'] = $this->params;
        }

        return $result;
    }
}
