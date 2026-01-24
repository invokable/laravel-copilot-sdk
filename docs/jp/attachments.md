# File Attachments

以下のようにファイルかディレクトリを配列で指定。

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
    ],
];

$response = Copilot::run(prompt: '...', attachments: $attachments);
```

こんなフォーマットを覚えてられないのでヘルパーを用意。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\Attachment;

$attachments = [
    Attachment::file(path: '/path/to/file.php', displayName: 'My File'),
    Attachment::directory(path: '/path/to/dir/', displayName: 'dir'),
];

$response = Copilot::run(prompt: '...', attachments: $attachments);
```
