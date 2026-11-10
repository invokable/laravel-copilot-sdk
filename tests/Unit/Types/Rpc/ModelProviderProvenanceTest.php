<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\ModelProviderProvenance;

describe('ModelProviderProvenance', function () {
    it('maps all provenance fields from an array', function () {
        $provenance = ModelProviderProvenance::fromArray([
            'adapterId' => 'openai-compatible',
            'providerKind' => 'openai',
            'source' => 'user',
            'ownerId' => 'user-123',
            'ownerDisplayName' => 'Example User',
        ]);

        expect($provenance->adapterId)->toBe('openai-compatible')
            ->and($provenance->providerKind)->toBe('openai')
            ->and($provenance->source)->toBe('user')
            ->and($provenance->ownerId)->toBe('user-123')
            ->and($provenance->ownerDisplayName)->toBe('Example User');
    });

    it('defaults missing required fields to empty strings and optional fields to null', function () {
        $provenance = ModelProviderProvenance::fromArray([]);

        expect($provenance->adapterId)->toBe('')
            ->and($provenance->providerKind)->toBe('')
            ->and($provenance->source)->toBe('')
            ->and($provenance->ownerId)->toBeNull()
            ->and($provenance->ownerDisplayName)->toBeNull()
            ->and($provenance->toArray())->toBe([
                'adapterId' => '',
                'providerKind' => '',
                'source' => '',
            ]);
    });

    it('round trips populated optional fields through an array', function () {
        $data = [
            'adapterId' => 'openai-compatible',
            'providerKind' => 'openai',
            'source' => 'user',
            'ownerId' => 'user-123',
            'ownerDisplayName' => 'Example User',
        ];

        expect(ModelProviderProvenance::fromArray($data)->toArray())->toBe($data);
    });
});
