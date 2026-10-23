<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;

/** Request to start a supervised host listener. */
readonly class HostStartRequest implements Arrayable
{
    public function __construct(
        public string $hostId,
        public ?string $computeId = null,
        public HostLocalServerOptions|array|null $localServer = null,
        public HostGitHubEnvironmentOptions|array|null $githubEnvironment = null,
        public ?bool $sessionFactory = null,
        public ?bool $resumeFactory = null,
        public HostAuthentication|array|null $authentication = null,
        public ?bool $allowCreatingNewSessions = null,
        public ?string $workspace = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            hostId: $data['hostId'] ?? '',
            computeId: $data['computeId'] ?? null,
            localServer: isset($data['localServer'])
                ? ($data['localServer'] instanceof HostLocalServerOptions
                    ? $data['localServer']
                    : HostLocalServerOptions::fromArray($data['localServer']))
                : null,
            githubEnvironment: isset($data['githubEnvironment'])
                ? ($data['githubEnvironment'] instanceof HostGitHubEnvironmentOptions
                    ? $data['githubEnvironment']
                    : HostGitHubEnvironmentOptions::fromArray($data['githubEnvironment']))
                : null,
            sessionFactory: $data['sessionFactory'] ?? null,
            resumeFactory: $data['resumeFactory'] ?? null,
            authentication: isset($data['authentication'])
                ? ($data['authentication'] instanceof HostAuthentication
                    ? $data['authentication']
                    : HostAuthentication::fromArray($data['authentication']))
                : null,
            allowCreatingNewSessions: $data['allowCreatingNewSessions'] ?? null,
            workspace: $data['workspace'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'hostId' => $this->hostId,
            'computeId' => $this->computeId,
            'localServer' => $this->localServer instanceof HostLocalServerOptions
                ? $this->localServer->toArray()
                : $this->localServer,
            'githubEnvironment' => $this->githubEnvironment instanceof HostGitHubEnvironmentOptions
                ? $this->githubEnvironment->toArray()
                : $this->githubEnvironment,
            'sessionFactory' => $this->sessionFactory,
            'resumeFactory' => $this->resumeFactory,
            'authentication' => $this->authentication instanceof HostAuthentication
                ? $this->authentication->toArray()
                : $this->authentication,
            'allowCreatingNewSessions' => $this->allowCreatingNewSessions,
            'workspace' => $this->workspace,
        ], fn ($value) => $value !== null);
    }
}
