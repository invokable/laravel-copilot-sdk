# 認証

Copilot SDKは複数の認証方法をサポートします。用途に合わせて選択してください。

## 認証方法

| 方法                       | 主な用途         | Copilotサブスクリプション | 備考                        |
|--------------------------|--------------|------------------|---------------------------|
| GitHubログイン（CLI）          | 開発・手動実行      | 必要               | `copilot` CLIのログイン情報を利用   |
| OAuth GitHub App / PAT   | ユーザーごとのトークン  | 必要               | `github_token`で指定         |
| 環境変数                     | CI/CD・サーバー運用 | 必要               | `COPILOT_GITHUB_TOKEN` など |
| BYOK（Bring Your Own Key） | 自前のモデルプロバイダー | 不要               | カスタムプロバイダー設定              |
| Microsoft Entra ID       | 組み込みサインイン    | Entraアプリ登録が必要 | `entra.clientId`でホストアプリを指定 |

BYOKは [custom-providers.md](./custom-providers.md) を参照。

## Microsoft Entra ID

組み込みのMicrosoft Entraサインインとトークン更新には、ホストアプリの登録情報をクライアントに設定します。`clientId`にはEntraアプリ登録のアプリケーション（クライアント）IDを指定してください。型または配列で指定でき、空文字列と空白だけの値は受け付けません。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\EntraAuthConfig;

Copilot::useStdio([
    'entra' => new EntraAuthConfig(clientId: config('services.entra.client_id')),
])->run(prompt: 'Hello');
Copilot::stop();
```

配列で指定することもできます。

```php
Copilot::useStdio([
    'entra' => ['clientId' => config('services.entra.client_id')],
])->run(prompt: 'Hello');
Copilot::stop();
```

設定したIDは`server.connect`ハンドシェイクでランタイムに渡されます。Entra設定を指定した場合、`connect`に対応しない古いランタイムでは設定を黙って破棄せず、`This runtime does not support Entra client configuration.`例外を送出します。ランタイムを更新してください。他の認証方法は引き続き利用できます。

## ネットワークコールバック

`network`設定で、ランタイムのプロキシ解決とKerberos Negotiate認証をホスト側のコールバックに委譲できます。利用するCopilotランタイムは`network.setProvider` RPCをサポートしている必要があります。未対応の場合、クライアント起動時にランタイムの更新を案内する例外が発生します。コールバックにはリクエスト型と、キャンセルを確認できる`CancellationToken`が渡されます。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Support\CancellationToken;
use Revolution\Copilot\Types\Rpc\NetworkAuthenticateNegotiateRequest;
use Revolution\Copilot\Types\Rpc\NetworkResolveProxyRequest;

Copilot::useStdio([
    'network' => [
        'scope' => 'runtime',
        'resolveProxy' => function (NetworkResolveProxyRequest $request, CancellationToken $signal) use ($proxyResolver): ?string {
            return $proxyResolver->resolve($request->url, $signal);
        },
        'authenticateNegotiate' => function (NetworkAuthenticateNegotiateRequest $request, CancellationToken $signal) use ($credentials): ?string {
            return $credentials->acquire($request->spn, $signal);
        },
    ],
])->run(prompt: 'Hello');
Copilot::stop();
```

`resolveProxy`は`PROXY proxy.example:8080; DIRECT`のようなPAC形式の文字列、または直接接続する`DIRECT`を返します。`null`はランタイム自身にプロキシを解決させます。認証コールバックは`Negotiate `プレフィックスを含まない、単回使用のbase64 SPNEGOトークンを返し、資格情報がなければ`null`を返します。コールバックが例外を投げた場合はRPCが失敗し、直接接続にはフォールバックしません。SPNは資格情報を発行する前に検証してください。

`scope`は省略すると`connection`です。`connection`はこのクライアントが作成または再開したセッションのリクエストだけを対象にします。`runtime`は共有のバックグラウンド通信も対象にしますが、同時にこのscopeを保持できる接続は1つだけです。

旧形式の`kerberosAuth`と`kerberos_auth`もソース互換用に受け付け、`network`が未指定なら`authenticateNegotiate`コールバックに変換します。旧`mode: always`は利用できますが、`mode: fallback`は新しいネットワークAPIでは表現できないため、`network.authenticateNegotiate`への移行を促す`InvalidArgumentException`になります。旧形式も新しい`network.setProvider` RPCを使うため、Kerberos専用RPCだけを実装する古いランタイムでは利用できません。新旧の両方を指定した場合は`network`が優先され、旧設定は使われません。

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

`false`にすると、認証に使われるのは明示的に指定したトークン（`github_token`）だけで、環境変数トークン（`COPILOT_GITHUB_TOKEN`、`GH_TOKEN`、`GITHUB_TOKEN`）は無視されます。

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
