<?php

declare(strict_types=1);

use Revolution\Copilot\Types\Rpc\SessionCompactionCompactResult;
use Revolution\Copilot\Types\Rpc\SessionFleetStartParams;
use Revolution\Copilot\Types\Rpc\SessionFleetStartResult;
use Revolution\Copilot\Types\Rpc\SessionModeGetResult;
use Revolution\Copilot\Types\Rpc\SessionModelGetCurrentResult;
use Revolution\Copilot\Types\Rpc\SessionModelSwitchToParams;
use Revolution\Copilot\Types\Rpc\SessionModelSwitchToResult;
use Revolution\Copilot\Types\Rpc\SessionModeSetParams;
use Revolution\Copilot\Types\Rpc\SessionModeSetResult;
use Revolution\Copilot\Types\Rpc\SessionPlanReadResult;
use Revolution\Copilot\Types\Rpc\SessionPlanUpdateParams;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceCreateFileParams;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceListFilesResult;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceReadFileParams;
use Revolution\Copilot\Types\Rpc\SessionWorkspaceReadFileResult;

describe('SessionModelGetCurrentResult', function () {
    it('can be created from array', function () {
        $result = SessionModelGetCurrentResult::fromArray(['modelId' => 'gpt-4']);
        expect($result->modelId)->toBe('gpt-4');
    });

    it('can be created with null modelId', function () {
        $result = SessionModelGetCurrentResult::fromArray([]);
        expect($result->modelId)->toBeNull();
    });
});

describe('SessionModelSwitchToParams', function () {
    it('can be created and converted', function () {
        $params = new SessionModelSwitchToParams(modelId: 'gpt-4');
        expect($params->toArray())->toBe(['modelId' => 'gpt-4']);
    });
});

describe('SessionModelSwitchToResult', function () {
    it('can be created from array', function () {
        $result = SessionModelSwitchToResult::fromArray(['modelId' => 'gpt-4']);
        expect($result->modelId)->toBe('gpt-4');
    });
});

describe('SessionModeGetResult', function () {
    it('can be created from array', function () {
        $result = SessionModeGetResult::fromArray(['mode' => 'interactive']);
        expect($result->mode)->toBe('interactive');
    });

    it('can convert to array', function () {
        $result = new SessionModeGetResult(mode: 'plan');
        expect($result->toArray())->toBe(['mode' => 'plan']);
    });
});

describe('SessionModeSetParams', function () {
    it('can be created and converted', function () {
        $params = new SessionModeSetParams(mode: 'autopilot');
        expect($params->toArray())->toBe(['mode' => 'autopilot']);
    });
});

describe('SessionModeSetResult', function () {
    it('can be created from array', function () {
        $result = SessionModeSetResult::fromArray(['mode' => 'plan']);
        expect($result->mode)->toBe('plan');
    });
});

describe('SessionPlanReadResult', function () {
    it('can be created from array with content', function () {
        $result = SessionPlanReadResult::fromArray([
            'exists' => true,
            'content' => '# Plan',
        ]);

        expect($result->exists)->toBeTrue()
            ->and($result->content)->toBe('# Plan');
    });

    it('can be created from array without content', function () {
        $result = SessionPlanReadResult::fromArray(['exists' => false]);

        expect($result->exists)->toBeFalse()
            ->and($result->content)->toBeNull();
    });
});

describe('SessionPlanUpdateParams', function () {
    it('can be created and converted', function () {
        $params = new SessionPlanUpdateParams(content: '# Updated Plan');
        expect($params->toArray())->toBe(['content' => '# Updated Plan']);
    });
});

describe('SessionWorkspaceListFilesResult', function () {
    it('can be created from array', function () {
        $result = SessionWorkspaceListFilesResult::fromArray([
            'files' => ['file1.txt', 'file2.txt'],
        ]);

        expect($result->files)->toBe(['file1.txt', 'file2.txt']);
    });

    it('handles empty files list', function () {
        $result = SessionWorkspaceListFilesResult::fromArray([]);

        expect($result->files)->toBe([]);
    });
});

describe('SessionWorkspaceReadFileResult', function () {
    it('can be created from array', function () {
        $result = SessionWorkspaceReadFileResult::fromArray([
            'content' => 'file content',
        ]);

        expect($result->content)->toBe('file content');
    });
});

describe('SessionWorkspaceReadFileParams', function () {
    it('can be created and converted', function () {
        $params = new SessionWorkspaceReadFileParams(path: 'test.txt');
        expect($params->toArray())->toBe(['path' => 'test.txt']);
    });
});

describe('SessionWorkspaceCreateFileParams', function () {
    it('can be created and converted', function () {
        $params = new SessionWorkspaceCreateFileParams(path: 'test.txt', content: 'hello');
        expect($params->toArray())->toBe(['path' => 'test.txt', 'content' => 'hello']);
    });
});

describe('SessionFleetStartParams', function () {
    it('can be created with prompt', function () {
        $params = new SessionFleetStartParams(prompt: 'build it');
        expect($params->toArray())->toBe(['prompt' => 'build it']);
    });

    it('filters null prompt', function () {
        $params = new SessionFleetStartParams;
        expect($params->toArray())->toBe([]);
    });
});

describe('SessionFleetStartResult', function () {
    it('can be created from array', function () {
        $result = SessionFleetStartResult::fromArray(['started' => true]);
        expect($result->started)->toBeTrue();
    });
});

describe('SessionCompactionCompactResult', function () {
    it('can be created from array', function () {
        $result = SessionCompactionCompactResult::fromArray([
            'success' => true,
            'tokensRemoved' => 1000,
            'messagesRemoved' => 5,
        ]);

        expect($result->success)->toBeTrue()
            ->and($result->tokensRemoved)->toBe(1000)
            ->and($result->messagesRemoved)->toBe(5);
    });

    it('can convert to array', function () {
        $result = new SessionCompactionCompactResult(
            success: true,
            tokensRemoved: 500,
            messagesRemoved: 3,
        );

        expect($result->toArray())->toBe([
            'success' => true,
            'tokensRemoved' => 500,
            'messagesRemoved' => 3,
        ]);
    });
});
