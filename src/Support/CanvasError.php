<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use RuntimeException;

/**
 * A structured error returned from a canvas provider.
 *
 * @experimental This type is part of an experimental API and may change or be removed.
 */
class CanvasError extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function toEnvelope(): array
    {
        return [
            'code' => $this->errorCode,
            'message' => $this->getMessage(),
        ];
    }

    public static function noHandler(): self
    {
        return new self(
            'canvas_action_no_handler',
            'No handler implemented for this canvas action',
        );
    }
}
