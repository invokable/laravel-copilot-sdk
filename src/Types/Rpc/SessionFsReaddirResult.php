<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/**
 * Result of reading a directory via SessionFs.
 */
readonly class SessionFsReaddirResult implements Arrayable
{
    /**
     * @param  array<string>  $entries  Entry names in the directory
     * @param  ?SessionFsError  $error  Structured provider error, if the read failed
     */
    public function __construct(
        public array $entries,
        public ?SessionFsError $error = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            entries: Arr::array($data, 'entries', []),
            error: isset($data['error']) ? SessionFsError::fromArray($data['error']) : null,
        );
    }

    public function toArray(): array
    {
        $result = [
            'entries' => $this->entries,
        ];

        if ($this->error !== null) {
            $result['error'] = $this->error->toArray();
        }

        return $result;
    }
}
