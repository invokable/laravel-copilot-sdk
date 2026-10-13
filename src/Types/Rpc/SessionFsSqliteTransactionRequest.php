<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Statements to execute atomically against the per-session database.
 */
readonly class SessionFsSqliteTransactionRequest implements Arrayable
{
    /**
     * @param  string  $sessionId  Target session identifier
     * @param  list<SessionFsSqliteTransactionStatement>  $statements  Ordered SQL statements to execute in one transaction
     */
    public function __construct(
        public string $sessionId,
        public array $statements,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: Arr::string($data, 'sessionId', ''),
            statements: array_values(array_map(
                fn (SessionFsSqliteTransactionStatement|array $statement) => $statement instanceof SessionFsSqliteTransactionStatement
                    ? $statement
                    : SessionFsSqliteTransactionStatement::fromArray($statement),
                $data['statements'] ?? [],
            )),
        );
    }

    public function toArray(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'statements' => array_map(
                fn (SessionFsSqliteTransactionStatement $statement) => $statement->toArray(),
                $this->statements,
            ),
        ];
    }
}
