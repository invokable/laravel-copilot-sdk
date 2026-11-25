# ServerRpc 概要

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

ServerRpc のメソッドをカテゴリー別にまとめた概要です。使用例は rpc.md の索引から機能別ページを参照してください。

## 接続・モデル・ツール・アカウント

`ping()`と`connect()`で接続を確認・開始し、`registerExtensionLaunchProvider()`で拡張機能の起動プロバイダーを登録します。モデル一覧・価格単位・組み込みカタログは`models()`、利用可能なツール一覧は`tools()`から取得できます。`account()`ではクォータ、認証状態、ログインユーザーの一覧、ログイン・ログアウトを扱います。

`providersConfig()->addModel()`はサーバーの保存済みプロバイダー設定にモデルを追加する実験的なRPCです。プロバイダーが未保存の場合は作成され、`providerAdded`で確認できます。永続的な操作ですが、実行中のセッションは変更しません。

providersConfig の使用例は [設定・管理設定](server-settings.md) を参照してください。

## ファイルロギング（experimental）

`logging()->file()`は、ホストが許可した出力先を使うランタイムファイルロガーを提供します。`open()`でロガーを開き、`write()`で秘匿化されたレコードを追加し、`flush()`で書き込みの完了を待ちます。`close()`はフラッシュしてロガーを閉じ、クローズ後はその`logId`を再利用できません。

使用例は [ファイルロギング](server-logging.md) を参照してください。

## 記録済みトラジェクトリの分類（experimental）

`taskAnalytics()->classifyTrajectory()`はセッションを作成せず、記録済みのCopilotイベントまたはATIFデータを分類します。アーカイブされたツールは実行されず、会話履歴や分類テレメトリにも追加されません。`events`は入力順のまま渡し、`classifiers`、`maxConcurrentRequests`、`judge`、`previousResult`を指定できます。`validateOnly: true`なら推論せず、ローカル検証結果だけを取得できます。

使用例は [セッション・ホスト・環境](server-sessions.md) を参照してください。

## MCP・コネクター・Secrets

`mcp()`はMCP設定の一覧・追加・更新・削除、有効化、検出、レジストリ検索、インストール計画と適用を提供します。`connectors()`ではコネクター機能とアカウント、カタログを確認できます。サーバー側でのコネクター操作はアカウント単位の一覧取得・更新が中心です。実験的な`secrets()`では、`addFilterValues()`でホスト生成のシークレットをredaction filterに登録し、`filter()`でテキストやJSONをマスクし、`removeFilterValues()`で登録を解除できます。

実験的な`mcp()->secrets()`では、レガシーMCP設定のクレデンシャルを`save()`、`delete()`、`deleteServer()`で管理できます。これは上記のマスキング API とは別です。

secrets の使用例は [MCP・コネクター](server-mcp.md) を参照してください。

## Skills・Plugins・Agents・Instructions・Extensions

各グループから探索や利用可能なパスの取得、設定変更、再読み込みを行えます。`commands()`は組み込みコマンド一覧、`hooks()`はセッション開始前のフック探索を提供します。SkillsとPluginsは有効化・無効化やインストール管理に対応し、Agentsは組み込みエージェント情報の取得に加え、名前検証、ツール分類の一覧取得、定義Markdownの生成、ファイル作成を行えます。Extensionsは永続化された拡張機能を探索・有効化・無効化します。`lsp()`ではサーバー全体の言語サーバー設定の一覧を取得できます。

`plugins()->activate()` はビルトインプラグインのフックを実行し、`plugins()->marketplaces()->migrateRetiredDefault()` は必要な廃止済み既定マーケットプレイスを復元します。どちらも実験的 API です。

使用例は [Skills・Plugins・Agents・Instructions・Extensions](server-skills-plugins.md) を参照してください。

## カタログとインストール

`catalog()`で候補検索・製品の推奨取得・選択・フィード取得を行い、Skills・Plugins・MCPの各APIでインストール計画の確認、適用、状態照会やキャンセルを行います。計画と適用は別操作です。実際のインストール例は後半の各項目を参照してください。

## セッション・ホスト・実行環境

`sessions()`は永続化済みセッションの検索、イベント読み取り、サイズ確認、削除・整理、forkを扱います。Workspaceの作成・読み込みやリモート制御も利用できます。`host()`はホストの起動、セッション公開・一覧・公開解除、認証、受付ポリシー変更、破棄を行います。AHPホストの高レベルAPIは[AHPホスト](../ahp.md)を参照してください。`runtime()`はランタイムのライフサイクルを扱います。

`sessions()->hasEvents()`はセッションを開かず、ローカルの永続イベントログの有無を確認します。使用例は [セッション・ホスト・環境](server-sessions.md) を参照してください。

`sessions()->import()`はセマンティックなセッション JSONL を検証し、新しいローカルセッションとして取り込む実験的APIです。`dryRun: true`ではセッションを作成しません。使用例は [セッション・ホスト・環境](server-sessions.md) を参照してください。

## Memories（experimental）

`memories()->import()`はセマンティックなメモリ JSONL を検証し、サーバーのアカウント・リポジトリポリシーに従って保存します。`dryRun: true`ではレコードを送信しません。使用例は [Memories](server-memories.md) を参照してください。

## クラウドエージェントタスク（experimental）

`cloudTasks()`はクラウドコーディングエージェントのタスク作成・削除・取得、セッションログやリプレイの取得、共有状態の変更、タスクIDから開くセッションの解決を行います。各メソッドは対応するParams型または配列を受け取り、結果を型付きDTOで返します。作成結果の`ok`が`false`の場合、サービスによる拒否理由は`reason`と`message`、実行されたHTTPリクエストの失敗情報は`statusCode`、`errorMessage`、`errorPayloadJson`で確認できます。

使用例は [セッション・ホスト・環境](server-sessions.md) を参照してください。

## Git・環境・サンドボックス・ポリシー

`git()`からブランチや作業ディレクトリ、Git remoteの情報を取得し、`gitHubRepository()`と`gitHubOwners()`からGitHubリポジトリや所有者を探索できます。`environments()`はMission Control環境、`sandbox()`と`sandboxProxyCa()`はサンドボックス対応状況やProxy CAを扱います。Proxy CAは設定ディレクトリごとの作成・信頼・削除やローテーションのほか、インストール辞退の記録、証明書ファイルの信頼にも対応します。`contentExclusion()`は除外ポリシーの検証、`managedSettings()`と`userSettings()`は設定を扱います。設定ファイルの個別キー削除と表示用の値のマスキングは`settings()`から行えます。

`gitHubRepository()->getById()`はGitHubリポジトリIDからowner/nameを解決し、`lookupBranchPullRequest()`は指定したhead ownerとブランチを、優先順に指定したリポジトリで検索します。どちらも`authInfo`をそのまま渡します。`getById()`の結果は`repository`にowner/nameを含み、見つからない場合は`null`です。Pull Request検索の`status`は`found`、`no-token`、`no-pr`、`lookup-failed`のいずれかで、未知の値は文字列のまま保持されるため例外になりません。`pullRequest`は見つかった場合の連想配列で、それ以外は`null`です。

gitHubRepository の使用例は [Git・GitHub](server-git.md) を参照してください。

`gitHubFetch()`は認証付きGitHub APIリクエストと名前付き操作を、`gitHubRelease()`はリリースの検索・スナップショット・資産ダウンロードと進捗確認を提供します。Fetchでは`sessionId`でセッションの認証を選び、省略時はプロセスの認証が使われます。ホストが管理する認証バインディングを使う場合は`authOwnerId`を指定します。Releaseの`authInfo`はランタイムが管理する認証情報の選択子で、公開リリースの検索・ダウンロードは匿名でも実行できます。ダウンロード結果の`path`は利用後に呼び出し側で削除してください。

Fetchの`allocateRequestId()`、`request()`、`executeOperation()`と、Releaseの`abandonRequest()`、`getSnapshot()`、`downloadAsset()`はExtensionsから呼び出せません。現在のLaravel SDKは両グループの`cancel` RPCを公開していないため、実行中の操作をSDKからキャンセルできません。`abandonRequest()`は未使用のRelease request IDを破棄する操作であり、実行中の操作を停止するものではありません。

`fetch()`は共有ネットワークトランスポートを使う汎用HTTPリクエスト用の実験的APIです。`create()`で接続所有のハンドルを作成し、必須の`responseMode`を指定して`send()`します。`stream`では`read()`で本文をチャンク単位に読み、`text`では本文全体をUTF-8テキストとして取得し、`file`ではランタイムホスト上の`destinationPath`へ本文を書き込みます。不要になったハンドルは`cancel()`で破棄します。認証情報を使わない場合、またはファイルをストリーミングする場合にも利用できます。対象URLに接続せずに候補プロキシ経路を確認する場合は`resolveProxy()`を使います。

## ローカルモデル・HTTP推論・ファイルシステム

`azureMl()`はローカルGPUメモリ情報の取得、モデルのカタログ・解決・ダウンロードと、その状態確認・キャンセル・削除を提供します。また、ファミリーリースを使ってAzure MLモデルのプロバイダー設定を登録・削除・復元できます。`llmInference()`はモデル層のHTTPリクエストをクライアント側へ中継する実験的APIです。`sessionFs()`はセッションのファイルシステム／SQLiteコールバックプロバイダーを登録します。

`localModels()`は接続単位の実験的APIで、モデル管理の設定・状態確認・変更監視、カタログ更新、モデルの準備・ダウンロード・更新・修復・削除、ジョブのキャンセル、通知の消去を行います。`configure()`、`inspect()`、`watch()`は`LocalModelsView`と`LocalModelsEvent[]`を含む型付き結果を返します。`watch()`には`CancellationToken`を渡せます。トークンをキャンセルするとJSON-RPCのキャンセル通知を送信し、接続は維持されます。`configure()`の`sessionId`は`null`の場合に送信されません。

## ネットワークコールバック（experimental）

`network()->setProvider()`で、クライアント側に登録したプロキシ解決・Kerberos認証コールバックをランタイムに通知します。通常は`Client`の`network`オプションから自動登録されるため、直接呼び出す必要はありません。`NetworkSetProviderRequest`のコールバック項目は、対応するハンドラーが登録済みであることを示します。RPCの戻り値はありません。利用するランタイムは`network.setProvider` RPCをサポートしている必要があり、未対応の場合はクライアント起動時に更新を案内する例外が発生します。

使用例は [HTTP推論・ファイルシステム](server-inference.md) を参照してください。

## 内部・実験的な RPC

公式SDKで内部用とされる次のRPCも、Laravelでは`@experimental`として公開しています。将来変更・削除される可能性があり、ランタイムの呼び出し元制限も適用されます。エージェントレジストリの作成・一覧・削除、認証URLのブラウザ起動、期限切れの大きなツール出力の整理、サンドボックスのパス許可警告を利用できます。一覧結果には接続資格情報は含まれません。

使用例は [モデル・ツール・アカウント](server-models.md)、[セッション・ホスト・環境](server-sessions.md)、[Skills・Plugins・Agents・Instructions・Extensions](server-skills-plugins.md) を参照してください。
