# SessionRpc 概要

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

SessionRpc のメソッドをカテゴリー別にまとめた概要です。使用例は rpc.md の索引から機能別ページを参照してください。

## モデル・モード・名前・プラン

`model()`で現在のモデル確認や切り替え、reasoning effort・Auto tier・許可モデルの設定を行えます。`mode()`はモード、`name()`はセッション名、`plan()`はプラン本文とSQLite todoの読み書きを扱います。

## ワークスペース・インストラクション・LSP

`workspaces()`はファイルの一覧・読み取り・書き込み・移動・削除やディレクトリ操作を提供します。`instructions()`でインストラクションの取得・再読み込み・探索、`lsp()`で言語サーバー設定の初期化・一覧取得・起動・停止・診断・有効状態の切り替えを行えます。

使用例は [ワークスペース・Agent・Fleet・Skills](session-workspace.md) を参照してください。

## Autopilot objective

`autopilotObjective()->getState()`はセッションの現在のAutopilot objective状態を型付きで返します。objectiveが存在しない場合、`state`は`null`です。Workspaceに保存されたobjectiveの有無を確認する`workspaces()->autopilotObjectiveExists()`とは異なるAPIです。

使用例は [ワークスペース・Agent・Fleet・Skills](session-workspace.md) を参照してください。

## エージェント・Fleet・バックグラウンドタスク

`agent()`でエージェントの一覧・選択・再読み込み、`fleet()`でFleetを開始します。`tasks()`ではバックグラウンドエージェントやクライアント所有タスクの登録、進捗取得・更新、移行、メッセージ送信、キャンセル、削除を扱います。

## MCP・Plugins・Extensions・Workflow

`mcp()`はサーバーの一覧・起動・停止・再読み込み、ツール・プロンプト・リソース、OAuth、sampling、動的ヘッダーの更新を扱います。`plugins()->checkUpdate()`では、インストール済みプラグインの更新有無をキャッシュ済みカタログと比較できます。`plugins()->marketplaces()->checkUpdate()`はマーケットプレイスの更新有無を確認し、`search()`は設定済みマーケットプレイスを横断してプラグインを検索します。`plugins()`と`extensions()`はセッション内の状態を管理し、`customizations()`はカスタマイズの再読み込みを行います。`workflow()`でワークフローを実行・照会し、`diagnostics()`と`connectors()`で診断情報やコネクターを取得できます。

## Tools・Permissions・Commands・UI

`tools()`と`permissions()`はイベントで届いた保留中リクエストへの応答や設定を扱います。権限の読み取りには`getAllowAll()`、`getRequired()`、`managedReviewRequired()`、`additionalDirectories()->list()`を利用できます。`permissions()->clearResumeEscalation()`は再開時に履歴から復元された権限昇格をメモリ内で解除し、成否を型付きで返します。`commands()`はコマンド一覧・実行・イベント応答、`ui()`はユーザー入力・elicitation・プラン承認などの要求への応答を提供します。`ui()->snapshot()`は保留中の要求、サービスやバックグラウンドエージェント、プラグイン有効化状態をまとめて取得します。`completions()`は入力候補を取得します。

`tools()->reportProgress()`は保留中の外部ツール呼び出しの進捗を報告します（experimental）。

`permissions()->folderTrust()->addSessionTrusted()`は保存済みの信頼フォルダ一覧を変更せず、現在の作業ディレクトリをセッション内で信頼します（experimental）。

使用例は [Tools・Permissions・Commands・UI](session-tools.md) を参照してください。

## Accounts・認証・Providers

`accounts()`は`list()`によるアカウント一覧、`getActive()`による選択中アカウントの取得、`lastErrors()`による認証エラーの取得、`switchActive()`・`logout()`・`setCredentials()`によるアカウント操作を扱います。`loginList()`はログインに再利用できる保存済みIDを取得し、`begin()`・`advance()`・`cancel()`は対話型ログインを扱います。`authProviders()->list()`は認証プロバイダーを一覧します。これらのRPCはexperimentalです。

`gitHubAuth()`はセッションの認証状態と資格情報、`provider()`と`providers()`はモデル接続先やプロバイダーアダプター、`connectors()`はセッションのコネクター接続を扱います。使用例は [ログ・シェル・使用量・テレメトリ・認証](session-activity.md) を参照してください。

## 履歴・キュー・ログ・使用量

`history()`は会話の圧縮（圧縮設定を含む）・中断・引き継ぎ要約・切り詰め、`queue()`は保留項目の挿入・移動・編集・削除・即時送信を提供します。`log()`、`eventLog()`、`usage()`、`quota()`、`limitPrediction()`でログやイベント、使用量と上限情報を扱います。`schedule()`ではスケジュール済みプロンプトを確認・停止できます。

`history()->importConversation()`は外部の会話イベントを順番に履歴へ取り込みます（experimental）。使用例は [管理設定・debug・履歴](session-settings.md#history-会話履歴の取り込み-experimental) を参照してください。

## セッションメモリ

`memory()->importRecord()`は、ユーザーが明示的に指定した1件のメモリをセッションのクラウドメモリサービスに取り込みます（experimental）。使用例とポリシーについては [セッションメモリ](session-memory.md) を参照してください。

## ライフサイクル・リモート・ホスト連携

`sendMessages()`は複数メッセージを1ターンで実行し、`suspend()`・`shutdown()`でセッションを停止します。`interruptMainTurn()`はメインターン、`cancelAllBackgroundAgents()`はバックグラウンドエージェントを個別に制御します。`remote()`と`visibility()`はリモート公開・共有状態、`sandbox()`はセッションのサンドボックス、`managedSettings()`はセッションスコープの管理設定、`contentExclusion()`はパス判定、`metadata()`はクライアント所有メタデータ、`canvas()`はCanvas連携を扱います。

`sandbox()->getState()`は実効設定と一時的な選択を取得し、`resetSessionChoices()`は一時的な選択をクリアして保存済み設定を管理ポリシーの下で採用します。使用例は [リモート・サンドボックス](session-remote.md) を参照してください。

`managedSettings()->refresh()`は管理設定を再取得し、追跡中のポリシー適用を待ちます。使用例は [管理設定・debug・履歴](session-settings.md) を参照してください。
