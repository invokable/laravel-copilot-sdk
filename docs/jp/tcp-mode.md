# TCPモード

通常、SDKは各リクエストごとに新しいCopilot CLIプロセスを起動します（stdioモード）。TCPモードを使用すると、事前に起動したCopilot CLIサーバーに接続できます。

## TCPモードのメリット

- **パフォーマンス向上**: プロセス起動のオーバーヘッドがない
- **リソース共有**: 複数のLaravelプロセスで同一のCLIサーバーを共有
- **プロセス管理**: Laravel Forge/Cloudなどでバックグラウンドプロセスとして管理可能
- **デプロイ対応**: デプロイ時の自動再起動に対応

## 使い方

### 1. Copilot CLIサーバーを起動

```shell
copilot --headless --port 12345
```

### 2. 環境変数を設定

```dotenv
COPILOT_URL=tcp://127.0.0.1:12345
# COPILOT_URL=http://127.0.0.1:12345
# COPILOT_URL=127.0.0.1:12345        tcp://部分は実際には全く使われてないのでhttpで指定してもいいし省略も可能
# COPILOT_URL=12345                  ポートのみも可能。ホストは自動的に127.0.0.1に設定される
# COPILOT_URL=127.0.0.1 or localhost ホストのみ指定は127.0.0.1とlocalhostのみ可能。ポートはデフォルトの12345になる。
```

これだけで、SDKはstdioモードからTCPモードに切り替わります。

## 設定ファイル

`config/copilot.php`でTCP接続を設定できます。

```php
return [
    // TCP接続先URL（設定するとTCPモードになる）
    'url' => env('COPILOT_URL'),
    
    // 以下はstdioモード時のみ使用される
    'cli_path' => env('COPILOT_CLI_PATH', 'copilot'),
    'cli_args' => [],
    'cwd' => null,
    'log_level' => env('COPILOT_LOG_LEVEL', 'info'),
    
    // 両モード共通
    'timeout' => env('COPILOT_TIMEOUT', 60),
    'model' => env('COPILOT_MODEL'),
];
```

COPILOT_URLとCOPILOT_CLI_PATHを両方設定した場合はTCPモードが優先。

## 実行時のモード切り替え

通常は設定ファイルに従ってTCPモードかstdioモードかが自動で切り替わります。コード内で明示的に指定することも可能です。

```php
use Revolution\Copilot\Facades\Copilot;

// TCPモードに切り替え
$response = Copilot::useTcp(url: 'tcp://127.0.0.1:12345')->run(prompt: 'Hello, TCP mode!');
// 何も指定しなければ設定ファイルの値が使われる。
$response = Copilot::useTcp()->run(prompt: 'Hello, TCP mode!');

// stdioモードに切り替え
$stdio_config = [
    'cli_path' => 'copilot',
    'cli_args' => [],
    'cwd' => base_path(),
    'log_level' => 'info',
];
$response = Copilot::useStdio($stdio_config)->run(prompt: 'Hello, stdio mode!');

// 何も指定しなければ設定ファイルの値が使われる。両方設定している場合はTCPが優先なので通常はTCPを使い一時的に切り替える場合はこの方法で使える。
$response = Copilot::useStdio()->run(prompt: 'Hello, stdio mode!');
```

サーバーによってはTCPモードでは正常に動作しないのでキューで動かす処理はTCPモードで使用し、Httpリクエスト内での処理だけstdioモードで使用するなどの使い分けも可能です。

## Laravel Forge/Cloudでの運用

### Laravel Forge

1. **Daemonの作成**: Forge管理画面でDaemonを作成

   ```
   Command: copilot --headless --port 12345
   User: forge
   Directory: /home/forge/your-app
   ```

2. **環境変数の設定**: `.env`に`COPILOT_URL`を追加

3. **デプロイスクリプト**: デプロイ時にDaemonを再起動

現在のForgeではおそらく不要。

   ```shell
   sudo supervisorctl restart daemon-123456:*
   ```

### Laravel Cloud

Laravel Cloudのワーカー機能を使用してバックグラウンドプロセスとして実行できます。

詳細は [laravel-cloud.md](./laravel-cloud.md) を参照してください。

## 注意事項

### セキュリティ

- TCPサーバーはローカル接続（127.0.0.1）を推奨
- 外部公開する場合は適切なファイアウォール設定が必要

### 再接続

現在のバージョンでは自動再接続機能はありません。接続が切れた場合は例外がスローされます。

### モードの確認

プログラム内でどちらのモードか確認できます。

```php
use Revolution\Copilot\Facades\Copilot;

$client = Copilot::client();

if ($client->isTcpMode()) {
    // TCPモード
} else {
    // stdioモード
}
```

## トラブルシューティング

### 接続できない

1. Copilot CLIサーバーが起動しているか確認
   ```shell
   ps aux | grep "copilot --server"
   ```

2. ポートが正しいか確認
   ```shell
   netstat -an | grep 12345
   ```

3. ファイアウォール設定を確認

### タイムアウトエラー

`config/copilot.php`の`timeout`値を増やしてください。

```php
'timeout' => 120, // 2分
```
