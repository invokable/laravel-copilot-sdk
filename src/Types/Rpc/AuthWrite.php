<?php
declare(strict_types=1);
namespace Revolution\Copilot\Types\Rpc;
use Illuminate\Contracts\Support\Arrayable;
readonly class AuthWrite implements Arrayable
{
    public function __construct(public string $kind, public ?string $selectionId = null, public ?string $host = null, public ?string $login = null, public ?string $token = null) {}
    public function toArray(): array { return array_filter(['kind' => $this->kind, 'selectionId' => $this->selectionId, 'host' => $this->host, 'login' => $this->login, 'token' => $this->token], fn ($value) => $value !== null); }
}
