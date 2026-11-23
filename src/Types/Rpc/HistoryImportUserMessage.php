<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

/** @experimental */
readonly class HistoryImportUserMessage implements Arrayable
{
    /** @param  array<HistoryImportAttachment|array>|null  $attachments */
    public function __construct(public string $content, public ?array $attachments = null) {}

    public static function fromArray(array $data): self
    {
        return new self(
            content: Arr::string($data, 'content'),
            attachments: isset($data['attachments']) ? array_map(
                fn (HistoryImportAttachment|array $attachment) => $attachment instanceof HistoryImportAttachment
                    ? $attachment
                    : HistoryImportAttachment::fromArray($attachment),
                Arr::array($data, 'attachments'),
            ) : null,
        );
    }

    public function toArray(): array
    {
        $data = ['content' => $this->content];
        if ($this->attachments !== null) {
            $data['attachments'] = array_map(
                fn (HistoryImportAttachment|array $attachment) => ($attachment instanceof HistoryImportAttachment
                    ? $attachment
                    : HistoryImportAttachment::fromArray($attachment))->toArray(),
                $this->attachments,
            );
        }

        return $data;
    }
}
