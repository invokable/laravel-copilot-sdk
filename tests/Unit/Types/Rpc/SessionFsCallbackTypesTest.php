<?php

declare(strict_types=1);

use Revolution\Copilot\Enums\EntryType;
use Revolution\Copilot\Types\Rpc\SessionFsAppendFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsEntry;
use Revolution\Copilot\Types\Rpc\SessionFsExistsRequest;
use Revolution\Copilot\Types\Rpc\SessionFsExistsResult;
use Revolution\Copilot\Types\Rpc\SessionFsMkdirRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirResult;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirWithTypesRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReaddirWithTypesResult;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileRequest;
use Revolution\Copilot\Types\Rpc\SessionFsReadFileResult;
use Revolution\Copilot\Types\Rpc\SessionFsRenameRequest;
use Revolution\Copilot\Types\Rpc\SessionFsRmRequest;
use Revolution\Copilot\Types\Rpc\SessionFsStatRequest;
use Revolution\Copilot\Types\Rpc\SessionFsStatResult;
use Revolution\Copilot\Types\Rpc\SessionFsWriteFileRequest;

describe('SessionFsReadFileRequest', function () {
    it('can be created from array', function () {
        $params = SessionFsReadFileRequest::fromArray([
            'path' => '/tmp/test.txt',
            'sessionId' => 'session-123',
        ]);

        expect($params->path)->toBe('/tmp/test.txt')
            ->and($params->sessionId)->toBe('session-123');
    });

    it('converts to array', function () {
        $params = new SessionFsReadFileRequest(path: '/tmp/test.txt', sessionId: 'session-123');

        expect($params->toArray())->toBe([
            'path' => '/tmp/test.txt',
            'sessionId' => 'session-123',
        ]);
    });
});

describe('SessionFsReadFileResult', function () {
    it('can be created from array', function () {
        $result = SessionFsReadFileResult::fromArray(['content' => 'hello world']);

        expect($result->content)->toBe('hello world');
    });

    it('defaults content to empty string', function () {
        $result = SessionFsReadFileResult::fromArray([]);

        expect($result->content)->toBe('');
    });
});

describe('SessionFsWriteFileRequest', function () {
    it('can be created with all fields', function () {
        $params = SessionFsWriteFileRequest::fromArray([
            'content' => 'file content',
            'path' => '/tmp/output.txt',
            'sessionId' => 'session-456',
            'mode' => 438,
        ]);

        expect($params->content)->toBe('file content')
            ->and($params->path)->toBe('/tmp/output.txt')
            ->and($params->sessionId)->toBe('session-456')
            ->and($params->mode)->toBe(438);
    });

    it('defaults mode to null', function () {
        $params = SessionFsWriteFileRequest::fromArray([
            'content' => 'data',
            'path' => '/tmp/file',
            'sessionId' => 'sess',
        ]);

        expect($params->mode)->toBeNull();
    });
});

describe('SessionFsAppendFileRequest', function () {
    it('can be created from array', function () {
        $params = SessionFsAppendFileRequest::fromArray([
            'content' => 'appended data',
            'path' => '/tmp/log.txt',
            'sessionId' => 'session-789',
            'mode' => 420,
        ]);

        expect($params->content)->toBe('appended data')
            ->and($params->path)->toBe('/tmp/log.txt')
            ->and($params->sessionId)->toBe('session-789')
            ->and($params->mode)->toBe(420);
    });
});

describe('SessionFsExistsRequest', function () {
    it('can be created from array', function () {
        $params = SessionFsExistsRequest::fromArray([
            'path' => '/tmp/check.txt',
            'sessionId' => 'session-aaa',
        ]);

        expect($params->path)->toBe('/tmp/check.txt')
            ->and($params->sessionId)->toBe('session-aaa');
    });
});

describe('SessionFsExistsResult', function () {
    it('can be created from array', function () {
        $result = SessionFsExistsResult::fromArray(['exists' => true]);

        expect($result->exists)->toBeTrue();
    });

    it('defaults exists to false', function () {
        $result = SessionFsExistsResult::fromArray([]);

        expect($result->exists)->toBeFalse();
    });
});

describe('SessionFsStatRequest', function () {
    it('can be created from array', function () {
        $params = SessionFsStatRequest::fromArray([
            'path' => '/tmp/file.txt',
            'sessionId' => 'session-stat',
        ]);

        expect($params->path)->toBe('/tmp/file.txt')
            ->and($params->sessionId)->toBe('session-stat');
    });
});

describe('SessionFsStatResult', function () {
    it('can be created with all fields', function () {
        $result = SessionFsStatResult::fromArray([
            'birthtime' => '2024-01-01T00:00:00Z',
            'isDirectory' => false,
            'isFile' => true,
            'mtime' => '2024-01-02T00:00:00Z',
            'size' => 4096,
        ]);

        expect($result->birthtime)->toBe('2024-01-01T00:00:00Z')
            ->and($result->isDirectory)->toBeFalse()
            ->and($result->isFile)->toBeTrue()
            ->and($result->mtime)->toBe('2024-01-02T00:00:00Z')
            ->and($result->size)->toBe(4096);
    });

    it('handles default values', function () {
        $result = SessionFsStatResult::fromArray([]);

        expect($result->birthtime)->toBe('')
            ->and($result->isDirectory)->toBeFalse()
            ->and($result->isFile)->toBeFalse()
            ->and($result->mtime)->toBe('')
            ->and($result->size)->toBe(0);
    });

    it('converts to array', function () {
        $result = new SessionFsStatResult(
            birthtime: '2024-01-01T00:00:00Z',
            isDirectory: true,
            isFile: false,
            mtime: '2024-01-02T00:00:00Z',
            size: 512,
        );

        expect($result->toArray())->toBe([
            'birthtime' => '2024-01-01T00:00:00Z',
            'isDirectory' => true,
            'isFile' => false,
            'mtime' => '2024-01-02T00:00:00Z',
            'size' => 512,
        ]);
    });
});

describe('SessionFsMkdirRequest', function () {
    it('can be created with all fields', function () {
        $params = SessionFsMkdirRequest::fromArray([
            'path' => '/tmp/newdir',
            'sessionId' => 'session-mkdir',
            'mode' => 493,
            'recursive' => true,
        ]);

        expect($params->path)->toBe('/tmp/newdir')
            ->and($params->sessionId)->toBe('session-mkdir')
            ->and($params->mode)->toBe(493)
            ->and($params->recursive)->toBeTrue();
    });

    it('handles default values', function () {
        $params = SessionFsMkdirRequest::fromArray([
            'path' => '/tmp/dir',
            'sessionId' => 'sess',
        ]);

        expect($params->mode)->toBeNull()
            ->and($params->recursive)->toBeNull();
    });
});

describe('SessionFsReaddirRequest', function () {
    it('can be created from array', function () {
        $params = SessionFsReaddirRequest::fromArray([
            'path' => '/tmp/mydir',
            'sessionId' => 'session-readdir',
        ]);

        expect($params->path)->toBe('/tmp/mydir')
            ->and($params->sessionId)->toBe('session-readdir');
    });
});

describe('SessionFsReaddirResult', function () {
    it('can be created from array', function () {
        $result = SessionFsReaddirResult::fromArray([
            'entries' => ['file1.txt', 'file2.txt', 'dir1'],
        ]);

        expect($result->entries)->toBe(['file1.txt', 'file2.txt', 'dir1']);
    });

    it('defaults entries to empty array', function () {
        $result = SessionFsReaddirResult::fromArray([]);

        expect($result->entries)->toBe([]);
    });
});

describe('SessionFsReaddirWithTypesRequest', function () {
    it('can be created from array', function () {
        $params = SessionFsReaddirWithTypesRequest::fromArray([
            'path' => '/tmp/typedir',
            'sessionId' => 'session-types',
        ]);

        expect($params->path)->toBe('/tmp/typedir')
            ->and($params->sessionId)->toBe('session-types');
    });
});

describe('SessionFsReaddirWithTypesResult', function () {
    it('can be created from array with entries', function () {
        $result = SessionFsReaddirWithTypesResult::fromArray([
            'entries' => [
                ['name' => 'file.txt', 'type' => 'file'],
                ['name' => 'subdir', 'type' => 'directory'],
            ],
        ]);

        expect($result->entries)->toHaveCount(2)
            ->and($result->entries[0])->toBeInstanceOf(SessionFsEntry::class)
            ->and($result->entries[0]->name)->toBe('file.txt')
            ->and($result->entries[0]->type)->toBe(EntryType::File)
            ->and($result->entries[1]->name)->toBe('subdir')
            ->and($result->entries[1]->type)->toBe(EntryType::Directory);
    });

    it('defaults entries to empty array', function () {
        $result = SessionFsReaddirWithTypesResult::fromArray([]);

        expect($result->entries)->toBe([]);
    });

    it('converts to array', function () {
        $result = new SessionFsReaddirWithTypesResult(
            entries: [
                new SessionFsEntry(name: 'test.php', type: EntryType::File),
            ],
        );

        $array = $result->toArray();

        expect($array['entries'])->toHaveCount(1)
            ->and($array['entries'][0])->toBe(['name' => 'test.php', 'type' => 'file']);
    });
});

describe('SessionFsEntry', function () {
    it('can be created from array', function () {
        $entry = SessionFsEntry::fromArray([
            'name' => 'README.md',
            'type' => 'file',
        ]);

        expect($entry->name)->toBe('README.md')
            ->and($entry->type)->toBe(EntryType::File);
    });

    it('handles directory type', function () {
        $entry = SessionFsEntry::fromArray([
            'name' => 'src',
            'type' => 'directory',
        ]);

        expect($entry->type)->toBe(EntryType::Directory);
    });

    it('handles unknown type as string', function () {
        $entry = SessionFsEntry::fromArray([
            'name' => 'unknown',
            'type' => 'symlink',
        ]);

        expect($entry->type)->toBe('symlink');
    });

    it('converts to array', function () {
        $entry = new SessionFsEntry(name: 'file.txt', type: EntryType::File);

        expect($entry->toArray())->toBe(['name' => 'file.txt', 'type' => 'file']);
    });
});

describe('SessionFsRmRequest', function () {
    it('can be created with all fields', function () {
        $params = SessionFsRmRequest::fromArray([
            'path' => '/tmp/to-delete',
            'sessionId' => 'session-rm',
            'force' => true,
            'recursive' => true,
        ]);

        expect($params->path)->toBe('/tmp/to-delete')
            ->and($params->sessionId)->toBe('session-rm')
            ->and($params->force)->toBeTrue()
            ->and($params->recursive)->toBeTrue();
    });

    it('handles default values', function () {
        $params = SessionFsRmRequest::fromArray([
            'path' => '/tmp/file',
            'sessionId' => 'sess',
        ]);

        expect($params->force)->toBeNull()
            ->and($params->recursive)->toBeNull();
    });
});

describe('SessionFsRenameRequest', function () {
    it('can be created from array', function () {
        $params = SessionFsRenameRequest::fromArray([
            'src' => '/tmp/old.txt',
            'dest' => '/tmp/new.txt',
            'sessionId' => 'session-rename',
        ]);

        expect($params->src)->toBe('/tmp/old.txt')
            ->and($params->dest)->toBe('/tmp/new.txt')
            ->and($params->sessionId)->toBe('session-rename');
    });

    it('converts to array', function () {
        $params = new SessionFsRenameRequest(
            src: '/tmp/a.txt',
            dest: '/tmp/b.txt',
            sessionId: 'sess',
        );

        expect($params->toArray())->toBe([
            'src' => '/tmp/a.txt',
            'dest' => '/tmp/b.txt',
            'sessionId' => 'sess',
        ]);
    });
});
