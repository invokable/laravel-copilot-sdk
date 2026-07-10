<?php

declare(strict_types=1);

namespace Revolution\Copilot\Ai;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Ai\Contracts\Gateway\StepTextGateway;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Providers\Concerns\GeneratesText;
use Laravel\Ai\Providers\Concerns\HasTextGateway;
use Laravel\Ai\Providers\Concerns\StreamsText;
use Laravel\Ai\Providers\Provider;

/**
 * Laravel AI SDK Integration.
 */
class CopilotProvider extends Provider implements TextProvider
{
    use GeneratesText;
    use HasTextGateway;
    use StreamsText;

    public function __construct(
        protected array $config,
        protected Dispatcher $events,
    ) {}

    /**
     * Get the provider's text gateway.
     */
    public function textGateway(): StepTextGateway
    {
        return $this->textGateway ??= new CopilotGateway;
    }

    /**
     * Get the name of the default text model.
     */
    public function defaultTextModel(): string
    {
        return $this->config['models']['text']['default'] ?? 'gpt-5.6-terra';
    }

    /**
     * Get the name of the cheapest text model.
     */
    public function cheapestTextModel(): string
    {
        return $this->config['models']['text']['cheapest'] ?? 'gpt-5.6-luna';
    }

    /**
     * Get the name of the smartest text model.
     */
    public function smartestTextModel(): string
    {
        return $this->config['models']['text']['smartest'] ?? 'gpt-5.6-sol';
    }
}
