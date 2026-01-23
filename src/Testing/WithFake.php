<?php

declare(strict_types=1);

namespace Revolution\Copilot\Testing;

use Illuminate\Support\Str;
use PHPUnit\Framework\Assert as PHPUnit;
use Revolution\Copilot\Types\SessionEvent;

trait WithFake
{
    protected ?CopilotFake $fake = null;

    /**
     * Whether to prevent stray requests.
     */
    protected bool $preventStrayRequests = false;

    /**
     * Allowed methods when preventing stray requests.
     *
     * @var array<string>
     */
    protected array $allowedMethods = [];

    /**
     * Set up fake responses.
     *
     * @param  array<string, ResponseSequence|SessionEvent|string>|string|false|null  $responses
     */
    public function fake(array|string|false|null $responses): self
    {
        if ($responses === false) {
            $this->fake = null;

            return $this;
        }

        $this->fake = new CopilotFake()->fake($responses);

        return $this;
    }

    public function isFake(): bool
    {
        return ! is_null($this->fake);
    }

    /**
     * Prevent stray requests.
     *
     * @param  false|array<string>  $allow
     */
    public function preventStrayRequests(false|array $allow = []): self
    {
        if ($allow === false) {
            $this->preventStrayRequests = false;
            $this->allowedMethods = [];

            return $this;
        }

        $this->preventStrayRequests = true;
        $this->allowedMethods = $allow;

        return $this;
    }

    /**
     * Determine if stray requests are being prevented.
     */
    public function preventingStrayRequests(): bool
    {
        return $this->preventStrayRequests;
    }

    public function isAllowedMethod(string $method): bool
    {
        if (! $this->preventingStrayRequests()) {
            return true;
        }

        foreach ($this->allowedMethods as $pattern) {
            if (Str::is($pattern, $method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a response helper.
     */
    public function response(string $content): SessionEvent
    {
        return ResponseSequence::responseFromContent($content);
    }

    /**
     * Create a response sequence helper.
     */
    public function sequence(): ResponseSequence
    {
        return new ResponseSequence;
    }

    /**
     * Assert that a prompt was sent.
     */
    public function assertPrompt(string $pattern): self
    {
        $found = false;

        foreach ($this->recorded() as $record) {
            if ($this->fake->matchesPattern($record['prompt'], $pattern)) {
                $found = true;
                break;
            }
        }

        PHPUnit::assertTrue($found, "Failed asserting that a prompt matching [{$pattern}] was sent.");

        return $this;
    }

    /**
     * Assert that a prompt was NOT sent.
     */
    public function assertNotPrompt(string $pattern): self
    {
        $found = false;

        foreach ($this->recorded() as $record) {
            if ($this->fake->matchesPattern($record['prompt'], $pattern)) {
                $found = true;
                break;
            }
        }

        PHPUnit::assertFalse($found, "Failed asserting that a prompt matching [{$pattern}] was not sent.");

        return $this;
    }

    /**
     * Assert the number of prompts sent.
     */
    public function assertPromptCount(int $count): self
    {
        PHPUnit::assertCount($count, $this->recorded(), "Failed asserting that {$count} prompts were sent.");

        return $this;
    }

    /**
     * Assert that no prompts were sent.
     */
    public function assertNothingSent(): self
    {
        PHPUnit::assertEmpty($this->recorded(), 'Failed asserting that no prompts were sent.');

        return $this;
    }

    /**
     * Get all recorded prompts.
     *
     * @return array<array{prompt: string, attachments: ?array, mode: ?string}>
     */
    public function recorded(): array
    {
        return $this->fake->recorded;
    }
}
