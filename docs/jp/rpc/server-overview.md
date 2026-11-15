# ServerRpc 概要

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

ServerRpc のメソッドをカテゴリー別にまとめた概要です。使用例は rpc.md の索引から機能別ページを参照してください。

## 接続・モデル・ツール・アカウント

`ping()`と`connect()`で接続を確認・開始し、`registerExtensionLaunchProvider()`で拡張機能の起動プロバイダーを登録します。モデル一覧・価格単位・組み込みカタログは`models()`、利用可能なツール一覧は`tools()`から取得できます。`account()`ではクォータ、認証状態、ログインユーザーの一覧、ログイン・ログアウトを扱い、`accounts()`ではMicrosoft Entra IDのトークン取得を行います。

`providersConfig()->addModel()`はサーバーの保存済みプロバイダー設定にモデルを追加する実験的なRPCです。プロバイダーが未保存の場合は作成され、`providerAdded`で確認できます。永続的な操作ですが、実行中のセッションは変更しません。

```php
use Revolution\Copilot\Facades\Copilot;

$result = Copilot::client()->rpc()->providersConfig()->addModel([
    'provider' => ['name' => 'my-provider', 'baseUrl' => 'https://example.com/v1'],
    'model' => ['id' => 'my-model', 'provider' => 'my-provider'],
    // 'credential' => $apiKey,
]);

// $result->providerAdded
```

配列の代わりに`ProvidersConfigAddModelRequest`を渡せます。任意の`credential`はCLIによってOSキーチェーンに保存され、結果には返されません。

同じ`providersConfig()`から、保存済みの設定を読み取り・更新・削除できます。これらも実験的なAPIであり、実行中のセッションは変更しません。

```php
$config = Copilot::client()->rpc()->providersConfig();
$saved = $config->read(); // $saved->providers と $saved->models

$provider = $config->updateProvider([
    'provider' => ['name' => 'my-provider', 'baseUrl' => 'https://example.com/v2'],
    // 'credential' => $apiKey,
]); // $provider->providerName

$model = $config->updateModel([
    'model' => ['id' => 'my-model', 'provider' => 'my-provider', 'maxOutputTokens' => 4096],
]); // $model->providerName と $model->modelId

$removed = $config->removeModel([
    'providerName' => 'my-provider',
    'modelId' => 'my-model',
]); // $removed->providerRemoved
```

`read()`は保存順の設定を返しますが、認証情報、ヘッダー、コマンドなどの秘密情報は返しません。各プロバイダーの`credentialStatus`は`present`、`absent`、`unavailable`のいずれかです。`updateProvider()`は共通設定を差し替え、省略された認証情報、カスタムヘッダー、ローカルモデル設定を維持します。`updateModel()`は指定されたプロバイダー名とモデルIDのモデル定義を差し替えます。`removeModel()`が最後のモデルを削除した場合はプロバイダーと管理対象の認証情報も削除され、`providerRemoved`が`true`になります。各更新・削除には対応する`ProvidersConfigUpdateProviderRequest`、`ProvidersConfigUpdateModelRequest`、`ProvidersConfigRemoveModelRequest`も使用できます。

## 記録済みトラジェクトリの分類（experimental）

`taskAnalytics()->classifyTrajectory()`はセッションを作成せず、記録済みのCopilotイベントまたはATIFデータを分類します。アーカイブされたツールは実行されず、会話履歴や分類テレメトリにも追加されません。`events`は入力順のまま渡し、`classifiers`、`maxConcurrentRequests`、`judge`、`previousResult`を指定できます。`validateOnly: true`なら推論せず、ローカル検証結果だけを取得できます。

通常の分類では、イベント内容はGitHub CAPIの認証情報を使ったモデル処理に送られます。自動的な秘匿化やプロンプトインジェクション対策はないため、送信を承認したうえでアーカイブの出所を検証し、必要な機密情報を事前に除去してください。分類結果は信頼できる事実ではなく、認可やセキュリティ判断には使用しないでください。

```php
$result = Copilot::client()->rpc()->taskAnalytics()->classifyTrajectory([
    'events' => $events,
    'validateOnly' => true,
]);
```

## MCP・コネクター・Secrets

`mcp()`はMCP設定の一覧・追加・更新・削除、有効化、検出、レジストリ検索、インストール計画と適用を提供します。`connectors()`ではコネクター機能とアカウント、カタログを確認できます。サーバー側でのコネクター操作はアカウント単位の一覧取得・更新が中心です。実験的な`secrets()->addFilterValues()`は、ホストが生成した動的シークレットをCLIのredaction filterに登録し、session logsやexportsでマスクします。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\SecretsAddFilterValuesRequest;

$result = Copilot::client()->rpc()->secrets()->addFilterValues(
    new SecretsAddFilterValuesRequest(values: [$accessToken]),
);
// $result->ok

// 配列でも指定できます
Copilot::client()->rpc()->secrets()->addFilterValues([
    'values' => [$accessToken],
]);
```

このRPCはOAuthトークンを取得するものではなく、値はSDK内で永続化・追加ログ出力されません。ホスト側で取得した値を明示的に登録してください。

## Skills・Plugins・Agents・Instructions・Extensions

各グループから探索や利用可能なパスの取得、設定変更、再読み込みを行えます。`commands()`は組み込みコマンド一覧、`hooks()`はセッション開始前のフック探索を提供します。SkillsとPluginsは有効化・無効化やインストール管理に対応し、Agentsは組み込みエージェント情報も取得できます。Extensionsは永続化された拡張機能を探索・有効化・無効化します。

## カタログとインストール

`catalog()`で候補検索・製品の推奨取得・選択・フィード取得を行い、Skills・Plugins・MCPの各APIでインストール計画の確認、適用、状態照会やキャンセルを行います。計画と適用は別操作です。実際のインストール例は後半の各項目を参照してください。

## セッション・ホスト・実行環境

`sessions()`は永続化済みセッションの検索、イベント読み取り、サイズ確認、削除・整理、forkを扱います。Workspaceの作成・読み込みやリモート制御も利用できます。`host()`はホストの起動、セッション公開・一覧・公開解除、認証、受付ポリシー変更、破棄を行います。AHPホストの高レベルAPIは[AHPホスト](../ahp.md)を参照してください。`runtime()`はランタイムのライフサイクルを扱います。

## クラウドエージェントタスク（experimental）

`cloudTasks()`はクラウドコーディングエージェントのタスク作成・削除・取得、セッションログやリプレイの取得、共有状態の変更、タスクIDから開くセッションの解決を行います。各メソッドは対応するParams型または配列を受け取り、結果を型付きDTOで返します。作成結果の`ok`が`false`の場合、サービスによる拒否理由は`reason`と`message`、実行されたHTTPリクエストの失敗情報は`statusCode`、`errorMessage`、`errorPayloadJson`で確認できます。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\CloudTasksCreateParams;

$result = Copilot::client()->rpc()->cloudTasks()->create(
    new CloudTasksCreateParams(
        authInfo: $authInfo,
        integrationId: 'my-app',
        repositoryOwner: 'octo',
        repositoryName: 'example',
    ),
);

if (! $result->ok) {
    // $result->reason and $result->message describe a declined request.
    // $result->statusCode and $result->errorMessage describe an attempted HTTP request.
}
```

## Git・環境・サンドボックス・ポリシー

`git()`からブランチや作業ディレクトリ、Git remoteの情報を取得し、`gitHubRepository()`と`gitHubOwners()`からGitHubリポジトリや所有者を探索できます。`environments()`はMission Control環境、`sandbox()`と`sandboxProxyCa()`はサンドボックス対応状況やProxy CA、`contentExclusion()`は除外ポリシーの検証、`managedSettings()`と`userSettings()`は設定を扱います。設定ファイルの個別キー削除と表示用の値のマスキングは`settings()`から行えます。

`gitHubRepository()->getById()`はGitHubリポジトリIDからowner/nameを解決し、`lookupBranchPullRequest()`は指定したhead ownerとブランチを、優先順に指定したリポジトリで検索します。どちらも`authInfo`をそのまま渡します。`getById()`の結果は`repository`にowner/nameを含み、見つからない場合は`null`です。Pull Request検索の`status`は`found`、`no-token`、`no-pr`、`lookup-failed`のいずれかで、未知の値は文字列のまま保持されるため例外になりません。`pullRequest`は見つかった場合の連想配列で、それ以外は`null`です。

```php
use Revolution\Copilot\Facades\Copilot;

$repository = Copilot::client()->rpc()->gitHubRepository();

$resolved = $repository->getById([
    'authInfo' => $authInfo,
    'repositoryId' => 12345,
]);

$lookup = $repository->lookupBranchPullRequest([
    'authInfo' => $authInfo,
    'headOwner' => 'octo',
    'baseRepositories' => [
        ['owner' => 'octo', 'name' => 'demo'],
        ['owner' => 'upstream', 'name' => 'demo'],
    ],
    'branch' => 'feature',
    'includeClosed' => false,
]);
```

`gitHubFetch()`は認証付きGitHub APIリクエストと名前付き操作を、`gitHubRelease()`はリリースの検索・スナップショット・資産ダウンロードと進捗確認を提供します。Fetchでは`sessionId`でセッションの認証を選び、省略時はプロセスの認証が使われます。ホストが管理する認証バインディングを使う場合は`authOwnerId`を指定します。Releaseの`authInfo`はランタイムが管理する認証情報の選択子で、公開リリースの検索・ダウンロードは匿名でも実行できます。ダウンロード結果の`path`は利用後に呼び出し側で削除してください。

Fetchの`allocateRequestId()`、`request()`、`executeOperation()`と、Releaseの`abandonRequest()`、`getSnapshot()`、`downloadAsset()`はExtensionsから呼び出せません。現在のLaravel SDKは両グループの`cancel` RPCを公開していないため、実行中の操作をSDKからキャンセルできません。`abandonRequest()`は未使用のRelease request IDを破棄する操作であり、実行中の操作を停止するものではありません。

`fetch()`は共有ネットワークトランスポートを使う汎用HTTPリクエスト用の実験的APIです。`create()`で接続所有のハンドルを作成し、必須の`responseMode`を指定して`send()`します。`stream`では`read()`で本文をチャンク単位に読み、`text`では本文全体をUTF-8テキストとして取得し、`file`ではランタイムホスト上の`destinationPath`へ本文を書き込みます。不要になったハンドルは`cancel()`で破棄します。認証情報を使わない場合、またはファイルをストリーミングする場合にも利用できます。

## ローカルモデル・HTTP推論・ファイルシステム

`azureMl()`はローカルGPUメモリ情報の取得、モデルのカタログ・解決・ダウンロードと、その状態確認・キャンセル・削除を提供します。また、ファミリーリースを使ってAzure MLモデルのプロバイダー設定を登録・削除・復元できます。`llmInference()`はモデル層のHTTPリクエストをクライアント側へ中継する実験的APIです。`sessionFs()`はセッションのファイルシステム／SQLiteコールバックプロバイダーを登録します。

`localModels()`は接続単位の実験的APIで、モデル管理の設定・状態確認・変更監視、カタログ更新、モデルの準備・ダウンロード・更新・修復・削除、ジョブのキャンセル、通知の消去を行います。`configure()`、`inspect()`、`watch()`は`LocalModelsView`と`LocalModelsEvent[]`を含む型付き結果を返します。`watch()`には`CancellationToken`を渡せます。トークンをキャンセルするとJSON-RPCのキャンセル通知を送信し、接続は維持されます。`configure()`の`sessionId`は`null`の場合に送信されません。

## ネットワークコールバック（experimental）

`network()->setProvider()`で、クライアント側に登録したプロキシ解決・Kerberos認証コールバックをランタイムに通知します。通常は`Client`の`network`オプションから自動登録されるため、直接呼び出す必要はありません。`NetworkSetProviderRequest`のコールバック項目は、対応するハンドラーが登録済みであることを示します。RPCの戻り値はありません。利用するランタイムは`network.setProvider` RPCをサポートしている必要があり、未対応の場合はクライアント起動時に更新を案内する例外が発生します。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Enums\NetworkScope;
use Revolution\Copilot\Types\Rpc\NetworkSetProviderRequest;

Copilot::client()->rpc()->network()->setProvider(
    new NetworkSetProviderRequest(
        scope: NetworkScope::Runtime,
        resolveProxy: true,
        authenticateNegotiate: true,
    ),
);
```

`scope`は省略すると`connection`です。`runtime`は共有バックグラウンド通信も対象にしますが、同時にこのscopeを登録できる接続は1つだけです。

## 内部・実験的な RPC

公式SDKで内部用とされる次のRPCも、Laravelでは`@experimental`として公開しています。将来変更・削除される可能性があり、ランタイムの呼び出し元制限も適用されます。エージェントレジストリの作成・一覧・削除、認証URLのブラウザ起動、期限切れの大きなツール出力の整理、サンドボックスのパス許可警告を利用できます。一覧結果には接続資格情報は含まれません。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\AgentRegistryEnsureDirRequest;
use Revolution\Copilot\Types\Rpc\AgentRegistryListEntriesRequest;
use Revolution\Copilot\Types\Rpc\AgentRegistryRemoveEntryRequest;
use Revolution\Copilot\Types\Rpc\RuntimeOpenBrowserRequest;
use Revolution\Copilot\Types\Rpc\SandboxPathGrantWarningParams;

$rpc = Copilot::client()->rpc();
$homeDir = '/home/user';

$registry = $rpc->agentRegistry();
$registry->ensureDir(new AgentRegistryEnsureDirRequest(homeDir: $homeDir));
$entries = $registry->listEntries(new AgentRegistryListEntriesRequest(homeDir: $homeDir));
$registry->removeEntry(new AgentRegistryRemoveEntryRequest(
    homeDir: $homeDir,
    pid: 1234,
));

$rpc->runtime()->openBrowser(new RuntimeOpenBrowserRequest(
    url: 'https://example.com/authorize',
));
$rpc->tools()->largeOutput()->cleanup();

$warning = $rpc->sandbox()->getPathGrantWarning(new SandboxPathGrantWarningParams(
    resolvedPath: '/workspace/project',
    readOnly: false,
));
```
