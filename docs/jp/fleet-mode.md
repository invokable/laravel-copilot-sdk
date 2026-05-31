# Fleet Mode

Fleet Modeは、1つの親セッションから複数のサブエージェントを並列に動かすための実験的なオーケストレーション機能です。作業を独立した単位に分割できる場合に、親セッションが担当範囲を割り当て、各サブエージェントの結果を集約します。

Laravel版ではtyped RPC layerから`session.fleet.start`を呼び出せます。

## 向いている用途

- ファイル、パッケージ、言語SDKごとに分けられるリファクタリング
- 複数モジュールを別々に調べる調査タスク
- 差分、アラート、ドキュメントページ単位のレビュー
- 移行作業のように、各担当範囲を独立して検証できるタスク

逆に、前のステップの具体的な結果が次のステップに必須な処理、同じファイルを複数ワーカーが編集する処理、小さな単発タスクには向きません。

## Fleet Modeを開始する

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\FleetStartRequest;

Copilot::start(function (CopilotSession $session): void {
    $result = $session->rpc()->fleet()->start(
        new FleetStartRequest(
            prompt: 'docs/jpの各ページを独立して確認し、必要な更新だけ報告して',
        ),
    );

    if ($result->started) {
        info('Fleet mode started');
    }
});
```

配列でも指定できます。

```php
$result = $session->rpc()->fleet()->start([
    'prompt' => '各パッケージを独立してレビューし、最後にリスクをまとめて',
]);
```

`prompt`は任意です。省略するとランタイム側のFleet Mode指示だけで開始します。

## Plan Modeから開始する

Plan Mode UIを自前で扱っている場合は、`onExitPlanModeRequest`で`autopilot_fleet`を選ぶことで、承認済みプランからFleet Modeへ進められます。

```php
use Revolution\Copilot\Types\ExitPlanModeRequest;
use Revolution\Copilot\Types\ExitPlanModeResult;
use Revolution\Copilot\Types\SessionConfig;

$config = new SessionConfig(
    onExitPlanModeRequest: function (ExitPlanModeRequest $request): ExitPlanModeResult {
        return new ExitPlanModeResult(
            approved: true,
            selectedAction: 'autopilot_fleet',
        );
    },
);
```

`autopilot`は単一の自律ワーカー、`interactive`はユーザーが継続的に関与する運用、`autopilot_fleet`は独立した作業単位を並列に進める運用に使います。

## サブエージェントの協調

Fleet Modeは暗黙の共有メモリではなく、明示的な作業単位で協調します。親セッションがタスクを分割し、各サブエージェントは自分の担当範囲だけを処理し、完了またはブロック状態を返します。

作業単位を設計するときは以下を明確にしてください。

- 1ワーカーが担当する範囲
- そのワーカーが編集してよいファイルやディレクトリ
- 完了時に返すべき成果物や検証結果
- 他タスクとの依存関係

## 注意点

- Fleet RPCは生成RPC層の実験的な機能です。利用する場合はCopilot CLIとSDKのバージョンを合わせてください
- 競合しやすいファイル編集は避け、担当範囲を明確に分けてください
- 親セッション側で結果を集約する前提で、各ワーカーの報告形式をプロンプトに含めると扱いやすくなります

## 関連ドキュメント

- [Custom Agents](./custom-agents.md)
- [RPC](./rpc.md)
- [Streaming Events](./streaming-events.md)
