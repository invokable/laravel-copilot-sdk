<?php
declare(strict_types=1);
namespace Revolution\Copilot\Types\Rpc;
use Illuminate\Contracts\Support\Arrayable;
readonly class AuthLoginAdvanceRequest implements Arrayable
{
    public function __construct(public string $flowId, public ?string $input = null) {}
    public function toArray(): array { return array_filter(['flowId' => $this->flowId, 'input' => $this->input], fn ($value) => $value !== null); }
}
