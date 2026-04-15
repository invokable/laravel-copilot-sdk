<?php

declare(strict_types=1);

namespace Revolution\Copilot\Concerns\Session;

use Revolution\Copilot\Enums\ElicitationAction;
use Revolution\Copilot\Types\InputOptions;
use Revolution\Copilot\Types\Rpc\UIElicitationRequest;
use Revolution\Copilot\Types\Rpc\UIElicitationResponse;
use Revolution\Copilot\Types\SessionCapabilities;
use RuntimeException;

/**
 * Provides UI convenience methods for interactive elicitation dialogs.
 *
 * @internal
 */
trait HasUiApi
{
    protected SessionCapabilities $capabilities;

    /**
     * Set the host capabilities for this session.
     *
     * @internal
     */
    public function setCapabilities(?array $capabilities = null): void
    {
        $this->capabilities = $capabilities !== null
            ? SessionCapabilities::fromArray($capabilities)
            : new SessionCapabilities;
    }

    /**
     * Get the host capabilities for this session.
     */
    public function capabilities(): SessionCapabilities
    {
        return $this->capabilities ?? new SessionCapabilities;
    }

    /**
     * Send a raw elicitation request to the CLI host.
     *
     * @throws RuntimeException if the host does not support elicitation
     */
    public function elicitation(string $message, array $requestedSchema): UIElicitationResponse
    {
        $this->assertElicitation();

        return $this->rpc()->ui()->elicitation(
            new UIElicitationRequest(
                message: $message,
                requestedSchema: $requestedSchema,
            )
        );
    }

    /**
     * Show a confirmation dialog and return the user's boolean answer.
     * Returns `false` if the user declines or cancels.
     *
     * @throws RuntimeException if the host does not support elicitation
     */
    public function confirm(string $message): bool
    {
        $this->assertElicitation();

        $result = $this->rpc()->ui()->elicitation(
            new UIElicitationRequest(
                message: $message,
                requestedSchema: [
                    'type' => 'object',
                    'properties' => [
                        'confirmed' => ['type' => 'boolean', 'default' => true],
                    ],
                    'required' => ['confirmed'],
                ],
            )
        );

        return $result->action === ElicitationAction::ACCEPT
            && ($result->content['confirmed'] ?? false) === true;
    }

    /**
     * Show a selection dialog with the given options.
     * Returns the selected value, or `null` if the user declines/cancels.
     *
     * @param  string[]  $options
     *
     * @throws RuntimeException if the host does not support elicitation
     */
    public function select(string $message, array $options): ?string
    {
        $this->assertElicitation();

        $result = $this->rpc()->ui()->elicitation(
            new UIElicitationRequest(
                message: $message,
                requestedSchema: [
                    'type' => 'object',
                    'properties' => [
                        'selection' => ['type' => 'string', 'enum' => $options],
                    ],
                    'required' => ['selection'],
                ],
            )
        );

        if ($result->action === ElicitationAction::ACCEPT && isset($result->content['selection'])) {
            return (string) $result->content['selection'];
        }

        return null;
    }

    /**
     * Show a text input dialog.
     * Returns the entered text, or `null` if the user declines/cancels.
     *
     * @throws RuntimeException if the host does not support elicitation
     */
    public function input(string $message, InputOptions|array|null $options = null): ?string
    {
        $this->assertElicitation();

        $options = match (true) {
            $options instanceof InputOptions => $options->toArray(),
            is_array($options) => $options,
            default => [],
        };

        $field = ['type' => 'string'];
        if (isset($options['title'])) {
            $field['title'] = $options['title'];
        }
        if (isset($options['description'])) {
            $field['description'] = $options['description'];
        }
        if (isset($options['minLength'])) {
            $field['minLength'] = $options['minLength'];
        }
        if (isset($options['maxLength'])) {
            $field['maxLength'] = $options['maxLength'];
        }
        if (isset($options['format'])) {
            $field['format'] = $options['format'];
        }
        if (isset($options['default'])) {
            $field['default'] = $options['default'];
        }

        $result = $this->rpc()->ui()->elicitation(
            new UIElicitationRequest(
                message: $message,
                requestedSchema: [
                    'type' => 'object',
                    'properties' => [
                        'value' => $field,
                    ],
                    'required' => ['value'],
                ],
            )
        );

        if ($result->action === ElicitationAction::ACCEPT && isset($result->content['value'])) {
            return (string) $result->content['value'];
        }

        return null;
    }

    /**
     * Assert that the host supports elicitation.
     *
     * @throws RuntimeException
     */
    protected function assertElicitation(): void
    {
        if (! $this->capabilities()->supportsElicitation()) {
            throw new RuntimeException('Elicitation is not supported by the host');
        }
    }

    /**
     * Merge updated capabilities from a capabilities.changed event.
     *
     * @internal
     */
    public function mergeCapabilities(array $data): void
    {
        $current = $this->capabilities()->toArray();
        $merged = array_replace_recursive($current, $data);
        $this->capabilities = SessionCapabilities::fromArray($merged);
    }
}
