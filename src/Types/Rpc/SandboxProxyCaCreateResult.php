<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

readonly class SandboxProxyCaCreateResult implements Arrayable
{
    public function __construct(public string $certificatePath) {}

    public static function fromArray(array $data): self
    {
        return new self(certificatePath: $data['certificatePath'] ?? '');
    }

    public function toArray(): array
    {
        return ['certificatePath' => $this->certificatePath];
    }
}
