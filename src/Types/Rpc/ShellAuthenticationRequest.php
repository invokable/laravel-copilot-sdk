<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Revolution\Copilot\Enums\ShellAuthenticationInputKind;

/**
 * A prompt raised by a shell command for authentication input.
 */
readonly class ShellAuthenticationRequest implements Arrayable
{
    public function __construct(
        public string $requestId,
        public string $program,
        public string $prompt,
        public ShellAuthenticationInputKind|string $inputKind,
        public string $command,
        public string $cwd,
    ) {}

    public static function fromArray(array $data): static
    {
        $inputKind = Arr::string($data, 'inputKind');

        return new static(
            requestId: Arr::string($data, 'requestId'),
            program: Arr::string($data, 'program'),
            prompt: Arr::string($data, 'prompt'),
            inputKind: ShellAuthenticationInputKind::tryFrom($inputKind) ?? $inputKind,
            command: Arr::string($data, 'command'),
            cwd: Arr::string($data, 'cwd'),
        );
    }

    public function toArray(): array
    {
        return [
            'requestId' => $this->requestId,
            'program' => $this->program,
            'prompt' => $this->prompt,
            'inputKind' => $this->inputKind instanceof ShellAuthenticationInputKind
                ? $this->inputKind->value
                : $this->inputKind,
            'command' => $this->command,
            'cwd' => $this->cwd,
        ];
    }
}
