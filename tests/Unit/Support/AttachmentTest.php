<?php

declare(strict_types=1);

use Revolution\Copilot\Support\Attachment;

describe('Attachment', function () {
    it('file', function () {
        expect(Attachment::file(path: '/path/to/file.txt', displayName: 'My File'))->toBe([
            'type' => 'file',
            'path' => '/path/to/file.txt',
            'displayName' => 'My File',
        ]);
    });

    it('directory', function () {
        expect(Attachment::directory(path: '/path/to/dir'))->toBe([
            'type' => 'directory',
            'path' => '/path/to/dir',
        ]);
    });

    it('selection', function () {
        expect(Attachment::selection(filePath: '/path/to/file.php', displayName: 'My File', selection: ['start' => ['line' => 1, 'character' => 10], 'end' => ['line' => 5, 'character' => 10]], text: '...'))->toBe([
            'type' => 'selection',
            'filePath' => '/path/to/file.php',
            'displayName' => 'My File',
            'selection' => ['start' => ['line' => 1, 'character' => 10], 'end' => ['line' => 5, 'character' => 10]],
            'text' => '...',
        ]);
    });
});
