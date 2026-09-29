<?php
declare(strict_types=1);
namespace Revolution\Copilot\Types\Rpc;
use Illuminate\Contracts\Support\Arrayable;
readonly class AuthLoginCancelRequest implements Arrayable
{
    public function __construct(public string $flowId) {}
    public function toArray(): array { return ['flowId' => $this->flowId]; }
}
