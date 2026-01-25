<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Closure;

interface Transport
{
    public function start(): void;

    public function stop(): void;

    public function send(string $message): void;

    public function read(float $timeout = 0.1): string;

    public function stream(Closure $stream): void;
}
