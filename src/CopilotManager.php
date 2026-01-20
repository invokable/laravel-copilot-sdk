<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Revolution\Copilot\Contracts\Factory;
use Revolution\Copilot\Types\SessionEvent;

/**
 * Copilot Manager - Laravel-friendly wrapper for CopilotClient.
 */
class CopilotManager implements Factory
{
    protected ?CopilotClient $client = null;

    public function __construct(
        protected array $config = [],
    ) {}

    /**
     * Run a single prompt and return the response.
     */
    public function run(string $prompt, array $options = []): ?SessionEvent
    {
        return $this->start(
            fn (CopilotSession $session) => $session->sendAndWait(
                array_merge(['prompt' => $prompt], $options),
                $this->config['timeout'] ?? 60.0,
            ),
        );
    }

    /**
     * Start a session and execute a callback.
     *
     * @param  callable(CopilotSession): mixed  $callback
     */
    public function start(callable $callback, array $config = []): mixed
    {
        $client = $this->getClient();
        $session = $client->createSession(array_merge(
            ['model' => $this->config['model'] ?? null],
            $config,
        ));

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
            $this->client = new CopilotClient([
                'cli_path' => $this->config['cli_path'] ?? 'copilot',
                'cli_args' => $this->config['cli_args'] ?? [],
                'cwd' => $this->config['cwd'] ?? base_path(),
                'log_level' => $this->config['log_level'] ?? 'info',
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
