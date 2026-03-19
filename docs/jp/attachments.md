# File Attachments

以下のようにファイルかディレクトリを配列で指定します。

```php
use Revolution\Copilot\Facades\Copilot;

$attachments = [
    [
        'type' => 'file',
        'path' => '/path/to/file.php',
        'displayName' => 'My File',// 省略可
    ],
    [
        'type' => 'directory',
        'path' => '/path/to/dir/',
        'displayName' => 'dir',// 省略可
    ],
    [
        'type' => 'selection',
        'filePath' => '/path/to/file.php',
        'displayName' => 'My File',// ここは省略不可
        'selection' => [
            'start' => ['line' => 1, 'character' => 10],
            'end' => ['line' => 5, 'character' => 10],
        ],
        'text' => '...',
    ],
    [
        'type' => 'blob',
        'data' => base64_encode(file_get_contents('/path/to/image.png')),
        'mimeType' => 'image/png',
        'displayName' => 'screenshot.png',// 省略可
    ],
];

$response = Copilot::run(prompt: '...', attachments: $attachments);
```

こんなフォーマットを覚えるのは難しいので簡単に使えるようにヘルパーを用意しています。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\Attachment;

$attachments = [
    Attachment::file(path: '/path/to/file.php', displayName: 'My File'),
    Attachment::directory(path: '/path/to/dir/', displayName: 'dir'),
    Attachment::selection(filePath: '/path/to/file.php', displayName: 'My File', selection: ['start' => ['line' => 1, 'character' => 10], 'end' => ['line' => 5, 'character' => 10]], text: '...'),
    Attachment::blob(data: base64_encode(file_get_contents('/path/to/image.png')), mimeType: 'image/png', displayName: 'screenshot.png'),
];

$response = Copilot::run(prompt: '...', attachments: $attachments);
```

## Blob Attachments

`blob`タイプはBase64エンコードされたデータをインラインで添付するために使います。ディスクI/Oなしで画像などのバイナリデータを直接送信できます。

```php
use Revolution\Copilot\Support\Attachment;

// 画像ファイルをBase64エンコードして添付
$attachment = Attachment::blob(
    data: base64_encode(file_get_contents('/path/to/image.png')),
    mimeType: 'image/png',
    displayName: 'screenshot.png',
);
```

selectionは公式SDKにもまだドキュメントがないので詳細は不明です。

github_referenceも公式に情報がないので後で対応します。
