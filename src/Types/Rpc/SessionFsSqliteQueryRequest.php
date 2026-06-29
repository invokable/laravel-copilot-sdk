<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\SessionFSSqliteQueryType;

/**
 * SQL query, query type, and optional bind parameters for executing a SQLite query
 * against the per-session database.
 */
readonly class SessionFsSqliteQueryRequest implements Arrayable
{
    /**
     * @param  string  $query  SQL query to execute
     * @param  SessionFSSqliteQueryType  $queryType  How to execute the query
     * @param  string  $sessionId  Target session identifier
     * @param  array<string, float|string|null>|null  $params  Optional named bind parameters
     */
    public function __construct(
        public string $query,
        public SessionFSSqliteQueryType $queryType,
        public string $sessionId,
        public ?array $params = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            query: Arr::string($data, 'query', ''),
            queryType: SessionFSSqliteQueryType::from($data['queryType']),
            sessionId: Arr::string($data, 'sessionId', ''),
            params: isset($data['params']) ? (array) $data['params'] : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'query' => $this->query,
            'queryType' => $this->queryType->value,
            'sessionId' => $this->sessionId,
        ];

        if ($this->params !== null) {
            $result['params'] = $this->params;
        }

        return $result;
    }
}
