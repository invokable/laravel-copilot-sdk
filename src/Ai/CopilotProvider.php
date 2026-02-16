<?php

declare(strict_types=1);

namespace Revolution\Copilot\Ai;

use Laravel\Ai\Contracts\Providers\FileProvider;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Providers\Concerns\GeneratesText;
use Laravel\Ai\Providers\Concerns\HasFileGateway;
use Laravel\Ai\Providers\Concerns\HasTextGateway;
use Laravel\Ai\Providers\Concerns\ManagesFiles;
use Laravel\Ai\Providers\Concerns\StreamsText;
use Laravel\Ai\Providers\Provider;

/**
 * Laravel AI SDK Integration.
 */
class CopilotProvider extends Provider implements FileProvider, TextProvider
{
    use GeneratesText;
    use HasFileGateway;
    use HasTextGateway;
    use ManagesFiles;
    use StreamsText;

    /**
     * Get the name of the default text model.
     */
    public function defaultTextModel(): string
    {
        return 'claude-sonnet-4.5';
    }

    /**
     * Get the name of the cheapest text model.
     */
    public function cheapestTextModel(): string
    {
        return 'claude-haiku-4-5';
    }

    /**
     * Get the name of the smartest text model.
     */
    public function smartestTextModel(): string
    {
        return 'claude-opus-4-6';
    }
}
