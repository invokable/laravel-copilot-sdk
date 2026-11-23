<?php

declare(strict_types=1);

namespace Revolution\Copilot\Types\Rpc;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use InvalidArgumentException;

readonly class UIPendingElicitation implements Arrayable
{
    public function __construct(
        public string $requestId,
        public UIElicitationPrompt $request,
        public ?string $elicitationSource = null,
        public ?string $elicitationSourceDisplayName = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $request = Arr::array($data, 'request', []);
        $prompt = match ($request['mode'] ?? null) {
            'form' => UIElicitationPromptForm::fromArray($request),
            'url' => UIElicitationPromptUrl::fromArray($request),
            default => throw new InvalidArgumentException('Unsupported UI elicitation prompt mode.'),
        };

        return new self(
            requestId: Arr::string($data, 'requestId', ''),
            request: $prompt,
            elicitationSource: isset($data['elicitationSource']) ? Arr::string($data, 'elicitationSource') : null,
            elicitationSourceDisplayName: isset($data['elicitationSourceDisplayName']) ? Arr::string($data, 'elicitationSourceDisplayName') : null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'requestId' => $this->requestId,
            'request' => $this->request->toArray(),
            'elicitationSource' => $this->elicitationSource,
            'elicitationSourceDisplayName' => $this->elicitationSourceDisplayName,
        ], static fn ($value): bool => $value !== null);
    }
}
