# 認証

Copilot SDKは複数の認証方法をサポートします。用途に合わせて選択してください。

## 認証方法

| 方法                       | 主な用途         | Copilotサブスクリプション | 備考                        |
|--------------------------|--------------|------------------|---------------------------|
| GitHubログイン（CLI）          | 開発・手動実行      | 必要               | `copilot` CLIのログイン情報を利用   |
| OAuth GitHub App / PAT   | ユーザーごとのトークン  | 必要               | `github_token`で指定         |
| 環境変数                     | CI/CD・サーバー運用 | 必要               | `COPILOT_GITHUB_TOKEN` など |
| BYOK（Bring Your Own Key） | 自前のモデルプロバイダー | 不要               | カスタムプロバイダー設定              |

BYOKは [custom-providers.md](./custom-providers.md) を参照。

## GitHubログイン（CLI）

`copilot` もしくは `gh` CLIでログイン済みの場合は、何も指定しなければ保存済みの認証情報が使われます。

```php
use Revolution\Copilot\Facades\Copilot;

$response = Copilot::run(prompt: 'Hello');
```

## OAuth GitHub App / PAT

OAuthフローで取得したユーザーアクセストークン、またはFine-grained PATを`github_token`に渡します。

- `gho_` / `ghu_` / `github_pat_` を想定
- `ghp_`（Classic PAT）は非推奨

```php
use Revolution\Copilot\Facades\Copilot;

$config = array_merge(config('copilot'), [
    'github_token' => $user->github_token,
]);

$response = Copilot::useStdio($config)->run(prompt: '...');
Copilot::stop();
```

`github_token`を指定すると`use_logged_in_user`は自動で`false`になります。明示的に切り替えたい場合は次の通り。

```php
$config = [
    'github_token' => $user->github_token,
    'use_logged_in_user' => true,
];
```

詳細は [github-token.md](./github-token.md) を参照。
`github_token`と`use_logged_in_user`はstdioモード専用です。TCPモードではCopilot CLI側で認証済みである必要があります。

## セッションごとのトークンプロバイダー

ユーザーごとに短命トークンを取得する場合は、`SessionConfig`の
`gitHubTokenProvider`にコールバックを指定できます。コールバックには`host`、
`sessionId`、`reason`（`initial`または`refresh`）が渡され、`token`を含む配列を返します。

```php
use Revolution\Copilot\Support\PermissionHandler;
use Revolution\Copilot\Types\SessionConfig;

$session = Copilot::client()->createSession(new SessionConfig(
    onPermissionRequest: PermissionHandler::approveAll(),
    gitHubTokenProvider: fn (array $request) => [
        'kind' => 'token',
        'accessToken' => $user->github_token,
        'expiresIn' => 3600,
    ],
));
```

`gitHubToken`と`gitHubTokenProvider`は同時に指定できません。

## ホスト生成シークレットのマスキング

MCP OAuthなどでホスト側が動的に取得したトークンを、Copilot CLIのredaction filterに登録できます。登録した値はsession logsやexportsでマスクされます。

```php
use Revolution\Copilot\Facades\Copilot;

$accessToken = $oauthProvider->getAccessToken();

Copilot::client()->rpc()->secrets()->addFilterValues([
    'values' => [$accessToken],
]);
```

これは`onMcpAuthRequest`によるトークン取得・受け渡しとは別の操作です。トークンを取得した後に明示的に呼び出してください。SDKは値を永続化したり、このRPCのために別途ログ出力したりしません。

## 環境変数

環境変数でトークンを渡す場合は、CLIが自動的に検出します。優先順位は上から順です。

1. `COPILOT_GITHUB_TOKEN`
2. `GH_TOKEN`
3. `GITHUB_TOKEN`

```dotenv
COPILOT_GITHUB_TOKEN=github_pat_xxx
```

```php
use Revolution\Copilot\Facades\Copilot;

$response = Copilot::run(prompt: 'Hello');
```

TCPモードではCLIサーバー側の環境変数を設定してください。

## 認証の優先順位

SDKは以下の順で認証情報を解決します。

1. `github_token`（明示指定）
2. 環境変数トークン（`COPILOT_GITHUB_TOKEN` → `GH_TOKEN` → `GITHUB_TOKEN`）
3. CLIに保存されたOAuth認証情報
4. `gh` CLIの認証情報

## 自動ログインを無効化

保存済みの認証情報を使いたくない場合は`use_logged_in_user`を`false`にします。

```php
use Revolution\Copilot\Facades\Copilot;

$response = Copilot::useStdio([
    'use_logged_in_user' => false,
])->run(prompt: 'Hello');
```

`github_token`と同様にstdioモードのみ対応です。TCPモードではCopilot CLI側で認証済みである必要があります。

## 認証状態の確認

`auth.getStatus`の結果を取得して確認できます。

```php
use Revolution\Copilot\Facades\Copilot;

$status = Copilot::client()->getAuthStatus();

if (! $status->isAuthenticated) {
    // 未認証
}
```

## 対話型ログインのアカウント選択

セッションRPCから`loginAccounts`を列挙すると、指定したプロバイダーで再利用できる保存済みIDを取得できます。各項目の`label`は表示用、`active`はそのIDが現在のセッションの有効なアカウントに対応するかを示します。`accountId`はプロバイダーが所有する不透明なIDです。内容を解析・加工せず、そのままログイン開始時に渡してください。

```php
use Revolution\Copilot\Enums\AuthLoginMode;
use Revolution\Copilot\Enums\LoginProviderKind;
use Revolution\Copilot\Types\Rpc\AuthLoginBeginRequest;

$accounts = $session->rpc()->accounts();
$identities = $accounts->enumerate(
    'loginAccounts',
    provider: LoginProviderKind::ENTRA,
);

if (isset($identities[0])) {
    $accounts->begin(new AuthLoginBeginRequest(
        kind: LoginProviderKind::ENTRA,
        mode: AuthLoginMode::Default,
        accountId: $identities[0]->accountId,
    ));
}
```

通常のプロバイダー動作で保存済みIDを使う場合は`mode: 'default'`を指定します。プロバイダーのアカウント選択UIを明示的に表示したい場合は`mode: 'selectAccount'`を使い、この場合は`accountId`を渡しません。既存の`begin(LoginProviderKind::ENTRA)`のような呼び出しも引き続き利用できます。
