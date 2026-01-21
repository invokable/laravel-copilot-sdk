<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Revolution\Copilot\Contracts\CopilotClient;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\Factory;
use Revolution\Copilot\Testing\WithFake;
use Revolution\Copilot\Types\SessionEvent;

/**
 * Copilot Manager - Laravel-friendly wrapper for CopilotClient.
 */
class CopilotManager implements Factory
{
    use WithFake;

    protected ?CopilotClient $client = null;

    public function __construct(
        protected array $config = [],
    ) {
        //
    }

    /**
     * Run a single prompt and return the response.
     */
    public function run(string $prompt, ?array $attachments = null, ?string $mode = null): ?SessionEvent
    {
        if ($this->isFake()) {
            return $this->fake->run($prompt, $attachments, $mode);
        }

        return $this->start(
            fn (CopilotSession $session) => $session->sendAndWait(
                prompt: $prompt,
                attachments: $attachments,
                mode: $mode,
                timeout: $this->config['timeout'] ?? 60.0,
            ),
        );
    }

    /**
     * Start a session and execute a callback.
     *
     * @param  callable(CopilotSession): mixed  $callback
     * @param  ?string  $resume  Session ID to resume
     */
    public function start(callable $callback, array $config = [], ?string $resume = null): mixed
    {
        if ($this->isFake()) {
            return $this->fake->start($callback, $config);
        }

        $client = $this->getClient();

        if (empty($resume)) {
            $session = $client->createSession(array_merge(
                ['model' => $this->config['model'] ?? null],
                $config,
            ));
        } else {
            $session = $client->resumeSession($resume, array_merge(
                ['model' => $this->config['model'] ?? null],
                $config,
            ));
        }

        try {
            return $callback($session);
        } finally {
            $session->destroy();
        }
    }

    /**
     * Create a new session (caller is responsible for destroying it).
     */
    public function createSession(array $config = []): CopilotSession
    {
        if ($this->isFake()) {
            return $this->fake->createSession($config);
        }

        return $this->getClient()->createSession(array_merge(
            ['model' => $this->config['model'] ?? null],
            $config,
        ));
    }

    /**
     * Get or create the CopilotClient instance.
     */
    public function getClient(): CopilotClient
    {
        if ($this->client === null) {
            $this->client = app(Client::class, [
                'options' => [
                    'cli_path' => $this->config['cli_path'] ?? null,
                    'cli_args' => $this->config['cli_args'] ?? [],
                    'cwd' => $this->config['cwd'] ?? base_path(),
                    'log_level' => $this->config['log_level'] ?? 'info',
                ],
            ]);

            $this->client->start();
        }

        return $this->client;
    }

    /**
     * Stop the client and release resources.
     */
    public function stop(): void
    {
        if ($this->client !== null) {
            $this->client->stop();
            $this->client = null;
        }
    }

    /**
     * Destructor - ensure client is stopped.
     */
    public function __destruct()
    {
        $this->stop();
    }
}
