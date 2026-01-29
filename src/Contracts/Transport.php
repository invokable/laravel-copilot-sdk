<?php

declare(strict_types=1);

namespace Revolution\Copilot\Contracts;

use Closure;

interface Transport
{
    public function start(): void;

    public function stop(): void;

    public function send(string $message): void;

    /**
     * Set handler for incoming data.
     *
     * @param  Closure(string): void  $handler
     */
    public function onReceive(Closure $handler): void;
}
