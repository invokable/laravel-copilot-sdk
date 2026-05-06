# Remote Sessions

Remote Sessionsを有効にすると、GitHubのMission Control経由でWebやモバイルから同じセッションにアクセスできます。  
このページは公式の`remote-sessions.md`をLaravel向けにまとめた日本語版です。

## 前提条件

- ユーザーが認証済みであること（GitHubトークンまたはログイン済みユーザー）
- `workingDirectory` がGitHubリポジトリであること

## クライアントレベルで有効化（常時ON）

Laravel版では、`config/copilot.php`に固定で追加するより、必要な処理だけ`useStdio()`で`remote: true`を有効化する使い方が扱いやすいです。

`remote`オプションはCLIプロセスをSDK側で起動する場合にのみ有効です。  
つまり、基本的に**stdioモード専用**です（`useTcp()`など外部サーバー接続時は無視されます）。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Enums\SessionEventType;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\SessionConfig;
use Revolution\Copilot\Types\SessionEvent;

$config = new SessionConfig(
    workingDirectory: '/path/to/github-repo',
    onPermissionRequest: PermissionHandler::approveAll(),
);

Copilot::useStdio(array_merge(config('copilot'), ['remote' => true]))->start(function (CopilotSession $session): void {
    $session->on(SessionEventType::SESSION_INFO, function (SessionEvent $event): void {
        $data = $event->all();

        if (($data['infoType'] ?? null) === 'remote') {
            echo 'Remote URL: '.($data['url'] ?? '').PHP_EOL;
        }
    });
}, config: $config);
```

## セッションごとに有効化（オンデマンド）

途中から共有を開始したい場合はRPCで切り替えます。  
CLIの`/remote on`と`/remote off`に相当します。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;

Copilot::start(function (CopilotSession $session): void {
    $result = $session->rpc()->remote()->enable();

    if ($result->url !== null) {
        echo 'Remote URL: '.$result->url.PHP_EOL;
    }

    // 共有停止
    $session->rpc()->remote()->disable();
});
```

## QRコード生成

取得したURLはQRコードにしてモバイルへ渡せます。  
Laravel Fortifyでも使われている[BaconQrCode](https://github.com/Bacon/BaconQRCode)が利用できます。

```php
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

$writer = new Writer(
    new ImageRenderer(
        new RendererStyle(256),
        new SvgImageBackEnd(),
    ),
);

$svg = $writer->writeString($result->url ?? '');
```

## 注意点

- `workingDirectory`がGitHubリポジトリでない場合、常時ONではリモート設定がスキップされます
- オンデマンド有効化（`remote()->enable()`）ではエラーになる場合があります
- 認証情報（`github_token`または`use_logged_in_user`）を適切に設定してください

## 参考

- 公式ドキュメント: https://github.com/github/copilot-sdk/blob/main/docs/features/remote-sessions.md
- [RPC](./rpc.md)
- [TCPモード](./tcp-mode.md)
