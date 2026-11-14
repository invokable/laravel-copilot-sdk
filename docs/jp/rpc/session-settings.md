# 管理設定・debug・履歴

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## debug (experimental)

`debug()->collectLogs()`でセッションのイベントログ、プロセスログ、シェルログを `.tgz` アーカイブまたはディレクトリに収集できます。ログは既定で秘匿情報がリダクションされます。`additionalEntries`でファイルやディレクトリを追加でき、`redaction`には`plain-text`、`events-jsonl`、`none`を指定できます。`none`を指定するとリダクションされないため、追加する内容に注意してください。

```php
use Revolution\Copilot\Enums\DebugCollectLogsEntryKind;
use Revolution\Copilot\Enums\DebugCollectLogsRedaction;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsDestination;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsEntry;
use Revolution\Copilot\Types\Rpc\DebugCollectLogsRequest;

$result = $session->rpc()->debug()->collectLogs(new DebugCollectLogsRequest(
    destination: DebugCollectLogsDestination::archive('/tmp/copilot-debug.tgz', noOverwrite: true),
    additionalEntries: [
        new DebugCollectLogsEntry(
            kind: DebugCollectLogsEntryKind::File,
            path: '/var/log/my-app.log',
            bundlePath: 'application/my-app.log',
            redaction: DebugCollectLogsRedaction::PlainText,
            required: true,
        ),
    ],
));
```

`DebugCollectLogsDestination::directory('/tmp/copilot-debug')`を指定するとステージングディレクトリに出力できます。`include`では収集するログや参照パスを個別に指定でき、結果の`entries`と`skippedEntries`から収集内容を確認できます。宛先・追加エントリのパスはCLIサーバー側のパスです。stdioでCLIを起動する標準構成ではアプリと同じホストですが、TCPで別ホストのCLIサーバーに接続している場合、呼び出し側マシンではなくサーバー側のパスを指定してください。

## contentExclusion (experimental)

セッションの作業ディレクトリ内にあるローカル絶対パスが、コンテンツ除外ポリシーの対象か確認できます。ファイルの内容を読み込んだり添付したりする前に呼び出してください。

```php
use Revolution\Copilot\Types\Rpc\ContentExclusionCheckPathsRequest;

$result = $session->rpc()->contentExclusion()->checkPaths(
    new ContentExclusionCheckPathsRequest(paths: [
        '/workspace/private.txt',
        '/workspace/public.txt',
    ]),
);

if ($result->isExcluded('/workspace/private.txt')) {
    // 除外対象、または安全に判定できません。
}
```

`checkPaths()` は絶対パスのみを受け付け、パスはセッションの作業ディレクトリ内にある必要があります。`checks` は入力と同じ順序で返り、重複したパスもそのまま保持されます。サポートされないパス、ファイルシステム、またはポリシー評価が利用できない場合は `available: false` と空の `checks` が返ります。この場合、`isExcluded()` は常に `true` を返すため、すべてのパスを除外対象として扱ってください。`available: true` でも結果にないパスは安全側に倒して除外対象と判定します。

```php
// managedSettings (experimental: セッションの管理設定)
// 現在保持されているスナップショットを取得（適用待ちや新しい取得は行わない）
$snapshot = $session->rpc()->managedSettings()->getCurrentSnapshot(); // ManagedSettingsResolvedData|null
// 現在のポリシー適用完了を待つ / ポリシーの公開完了だけを待つ
$session->rpc()->managedSettings()->awaitApplied();
$session->rpc()->managedSettings()->awaitPublished();
// Policy Helperを再評価し、適用後の実効設定を返す
$settings = $session->rpc()->managedSettings()->recheckPolicyHelper(); // ManagedSettingsResolvedData

// compaction → history に名前変更
$session->rpc()->history()->compact();
// 圧縮に使うモデル・指示文・推論強度を設定する（experimental）。呼ぶたびに3項目すべてが置き換わり、省略した項目はデフォルトに戻る
$session->rpc()->history()->setCompactionConfig(new HistorySetCompactionConfigRequest(
    model: 'gpt-5', // 任意。BYOK/providerセッションなどでは要約はセッションのモデルで行われる
    prompt: 'Keep decisions only', // 任意。組み込みプロンプトを置き換える
    reasoningEffort: ReasoningEffort::LOW, // 任意
));
// 手動圧縮を中断する / バックグラウンド圧縮をキャンセルする
$session->rpc()->history()->abortManualCompaction();
$session->rpc()->history()->cancelBackgroundCompaction();
// 別セッションへの引き継ぎ用要約を取得する
$summary = $session->rpc()->history()->summarizeForHandoff()->summary;
// 特定のイベント以降の履歴を切り詰める
$session->rpc()->history()->truncate(new HistoryTruncateRequest(
    eventId: 'evt-123', // このイベントとそれ以降のすべてのイベントが削除される
));
```
