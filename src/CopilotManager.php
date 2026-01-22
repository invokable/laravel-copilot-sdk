<?php

declare(strict_types=1);

namespace Revolution\Copilot;

use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Macroable;
use Revolution\Copilot\Contracts\CopilotClient;
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Contracts\Factory;
use Revolution\Copilot\Testing\WithFake;
use Revolution\Copilot\Types\ResumeSessionConfig;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;

/**
 * Copilot Manager - Laravel-friendly wrapper for CopilotClient.
 */
class CopilotManager implements Factory
{
    use Conditionable;
    use Macroable;
    use WithFake;

    protected ?CopilotClient $client = null;

    /**
     * The process ID that created this instance.
     * Used to prevent cleanup in forked child processes.
     */
    protected int $ownerPid;

    public function __construct(
        protected array $config = [],
    ) {
        $this->ownerPid = getmypid();
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
    public function start(callable $callback, SessionConfig|ResumeSessionConfig|array $config = [], ?string $resume = null): mixed
    {
        if ($this->isFake()) {
            return $this->fake->start($callback, $config);
        }

        $client = $this->getClient();

        $config = is_array($config) ? $config : $config->toArray();

        if (empty($resume)) {
            if (is_array($config)) {
                $config = SessionConfig::fromArray(array_merge(
                    ['model' => $this->config['model'] ?? null],
                    $config,
                ));
            }

            $session = $client->createSession($config);
        } else {
            if (is_array($config)) {
                $config = ResumeSessionConfig::fromArray(array_merge(
                    ['model' => $this->config['model'] ?? null],
                    $config,
                ));
            }

            $session = $client->resumeSession($resume, $config);
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
    public function createSession(SessionConfig|array $config = []): CopilotSession
    {
        if ($this->isFake()) {
            return $this->fake->createSession($config);
        }

        if (is_array($config)) {
            $config = SessionConfig::fromArray(array_merge(
                ['model' => $this->config['model'] ?? null],
                $config,
            ));
        }

        return $this->getClient()->createSession($config);
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
     *
     * Only cleanup if we're in the same process that created this instance.
     * This prevents forked child processes (e.g., from pcntl_fork() in Laravel Prompts spin())
     * from destroying sessions that belong to the parent process.
     */
    public function __destruct()
    {
        if (getmypid() === $this->ownerPid) {
            $this->stop();
        }
    }
}
