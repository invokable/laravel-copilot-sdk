<?php
declare(strict_types=1);
namespace Revolution\Copilot\Types\Rpc;
use Illuminate\Contracts\Support\Arrayable;
readonly class AuthWriteResult implements Arrayable
{
    public function __construct(public bool $ok, public ?bool $moreUsers = null) {}
    public static function fromArray(array $data): self { return new self((bool) ($data['ok'] ?? false), $data['moreUsers'] ?? null); }
    public function toArray(): array { return array_filter(['ok' => $this->ok, 'moreUsers' => $this->moreUsers], fn ($value) => $value !== null); }
}
