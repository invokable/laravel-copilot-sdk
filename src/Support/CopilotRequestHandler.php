<?php

declare(strict_types=1);

namespace Revolution\Copilot\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Base class for observing or replacing outbound HTTP model-layer requests.
 *
 * Override sendRequest() to customize forwarding. This API is experimental.
 */
class CopilotRequestHandler
{
    /**
     * Forward an intercepted HTTP request.
     *
     * @experimental
     */
    public function sendRequest(CopilotRequestContext $context, string $method, string $body): Response
    {
        $headers = array_filter(
            $context->headers,
            fn (array $values, string $name): bool => ! in_array(strtolower($name), [
                'host',
                'connection',
                'content-length',
                'transfer-encoding',
                'keep-alive',
                'upgrade',
                'proxy-connection',
                'te',
                'trailer',
            ], true),
            ARRAY_FILTER_USE_BOTH,
        );

        return Http::withHeaders($headers)
            ->connectTimeout(30)
            ->timeout(0)
            ->withOptions(['stream' => true, 'decode_content' => false])
            ->send($method, $context->url, ['body' => $body]);
    }
}
