<?php

declare(strict_types=1);

use Revolution\Copilot\Exceptions\JsonRpcException;

describe('JsonRpcException', function () {
    it('formats message with code and description', function () {
        $e = new JsonRpcException(-32601, 'Method not found');

        expect($e->getMessage())->toBe('JSON-RPC Error -32601: Method not found')
            ->and($e->code)->toBe(-32601)
            ->and($e->data)->toBeNull();
    });

    it('stores optional data', function () {
        $e = new JsonRpcException(-32602, 'Invalid params', ['field' => 'sessionId']);

        expect($e->data)->toBe(['field' => 'sessionId']);
    });

    it('adds diagnostics while preserving the original exception', function () {
        $original = new JsonRpcException(-32000, 'Timeout waiting for response');
        $withDiagnostic = $original->withDiagnostic('stderr: runtime stopped');

        expect($withDiagnostic)->toBeInstanceOf(JsonRpcException::class)
            ->and($withDiagnostic->getMessage())->toContain('stderr: runtime stopped')
            ->and($withDiagnostic->getPrevious())->toBe($original);
    });

    it('is an instance of Exception', function () {
        $e = new JsonRpcException(0, 'error');

        expect($e)->toBeInstanceOf(Exception::class);
    });
});
