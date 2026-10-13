<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\TaskClientActiveStatus;
use Revolution\Copilot\Enums\TaskClientUpdateKind;

/**
 * Progress or terminal update for a client-owned task.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class TaskClientUpdate implements Arrayable
{
    /**
     * @param  array<string>  $providedFields  Optional fields explicitly supplied as null
     */
    public function __construct(
        public TaskClientUpdateKind $kind,
        public ?TaskClientActiveStatus $status = null,
        public ?string $message = null,
        public ?string $phase = null,
        public int|float|null $percentage = null,
        public mixed $result = null,
        public ?string $error = null,
        public ?string $code = null,
        public ?string $reason = null,
        private array $providedFields = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            kind: TaskClientUpdateKind::from($data['kind'] ?? 'progress'),
            status: isset($data['status']) ? TaskClientActiveStatus::from($data['status']) : null,
            message: $data['message'] ?? null,
            phase: $data['phase'] ?? null,
            percentage: $data['percentage'] ?? null,
            result: $data['result'] ?? null,
            error: $data['error'] ?? null,
            code: $data['code'] ?? null,
            reason: $data['reason'] ?? null,
            providedFields: array_values(array_intersect(
                ['phase', 'percentage', 'result'],
                array_keys($data),
            )),
        );
    }

    public function toArray(): array
    {
        $result = ['kind' => $this->kind->value];

        if ($this->kind === TaskClientUpdateKind::Progress) {
            if ($this->status !== null) {
                $result['status'] = $this->status->value;
            }
            if ($this->message !== null) {
                $result['message'] = $this->message;
            }
            foreach (['phase' => $this->phase, 'percentage' => $this->percentage] as $key => $value) {
                if ($value !== null || in_array($key, $this->providedFields, true)) {
                    $result[$key] = $value;
                }
            }
        }

        if ($this->kind === TaskClientUpdateKind::Completed) {
            if ($this->message !== null) {
                $result['message'] = $this->message;
            }
            if ($this->result !== null || in_array('result', $this->providedFields, true)) {
                $result['result'] = $this->result;
            }
        }

        if ($this->kind === TaskClientUpdateKind::Failed) {
            if ($this->message !== null) {
                $result['message'] = $this->message;
            }
            if ($this->error !== null) {
                $result['error'] = $this->error;
            }
            if ($this->code !== null) {
                $result['code'] = $this->code;
            }
        }

        if ($this->kind === TaskClientUpdateKind::Cancelled) {
            if ($this->message !== null) {
                $result['message'] = $this->message;
            }
            if ($this->reason !== null) {
                $result['reason'] = $this->reason;
            }
        }

        return $result;
    }
}
