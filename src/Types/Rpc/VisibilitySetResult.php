<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Revolution\Copilot\Enums\SessionVisibilityStatus;

/**
 * Effective sharing status and shareable GitHub URL after updating session visibility.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
readonly class VisibilitySetResult implements Arrayable
{
    /**
     * @param  bool  $synced  Whether the session has been synced to Mission Control.
     * @param  SessionVisibilityStatus|null  $status  The effective visibility status.
     * @param  string|null  $shareUrl  Shareable GitHub URL for the session.
     */
    public function __construct(
        public bool $synced,
        public ?SessionVisibilityStatus $status = null,
        public ?string $shareUrl = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $statusRaw = $data['status'] ?? null;

        return new self(
            synced: (bool) ($data['synced'] ?? false),
            status: $statusRaw !== null ? SessionVisibilityStatus::tryFrom($statusRaw) : null,
            shareUrl: isset($data['shareUrl']) ? (string) $data['shareUrl'] : null,
        );
    }

    public function toArray(): array
    {
        $result = ['synced' => $this->synced];

        if ($this->status !== null) {
            $result['status'] = $this->status->value;
        }

        if ($this->shareUrl !== null) {
            $result['shareUrl'] = $this->shareUrl;
        }

        return $result;
    }
}
