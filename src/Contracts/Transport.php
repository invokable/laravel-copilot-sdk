<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

interface Transport
{
    public function send(string $message): void;

    public function read(float $timeout = 0.1): string;
}
