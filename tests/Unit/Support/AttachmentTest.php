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
});
