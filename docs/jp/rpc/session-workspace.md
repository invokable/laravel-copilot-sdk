# ワークスペース・Agent・Fleet・Skills

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
// workspaces
$session->rpc()->workspaces()->getWorkspace();
$session->rpc()->workspaces()->listFiles();
$session->rpc()->workspaces()->readFile(new WorkspacesReadFileRequest(path: 'file.txt'));
$session->rpc()->workspaces()->createFile(new WorkspacesCreateFileRequest(path: 'file.txt', content: '...'));
$session->rpc()->workspaces()->statFile(new WorkspacesStatFileRequest(path: 'file.txt'));
$session->rpc()->workspaces()->createDirectory(new WorkspacesCreateDirectoryRequest(path: 'new/dir', recursive: true));
$session->rpc()->workspaces()->removePath(new WorkspacesRemovePathRequest(path: 'old/dir', recursive: true, force: true));
$session->rpc()->workspaces()->renamePath(new WorkspacesRenamePathRequest(source: 'old.txt', destination: 'new.txt'));

// instructions (セッションのインストラクションソースを取得)
$result = $session->rpc()->instructions()->getSources();
// $result->sources - InstructionsSources の配列
$session->rpc()->instructions()->reload();

// LSP設定を明示的に再読み込み（experimental）
$session->rpc()->lsp()->initialize([
    'workingDirectory' => base_path(),
    'gitRoot' => base_path(),
    'force' => true,
]);

// instructions discover (インストラクションファイルを検出)
use Revolution\Copilot\Types\Rpc\InstructionsDiscoverRequest;

$result = $session->rpc()->instructions()->discover();
// $result->sources - InstructionSource の配列
$result = $session->rpc()->instructions()->discover(new InstructionsDiscoverRequest(
    projectPaths: ['/path/to/project'],
    excludeHostInstructions: true, // ホストレベルのインストラクションを除外
));

// fleet
$session->rpc()->fleet()->start(new FleetStartRequest(prompt: '...'));
// 詳細は fleet-mode.md を参照

// agent
$session->rpc()->agent()->list();
$session->rpc()->agent()->getCurrent();
$session->rpc()->agent()->select(new AgentSelectRequest(agentId: '...'));
$session->rpc()->agent()->deselect();
$session->rpc()->agent()->reload();

// skills (experimental: スキルの管理)
$session->rpc()->skills()->list();
$session->rpc()->skills()->enable(new SkillsEnableRequest(name: 'skill-name'));
$session->rpc()->skills()->disable(new SkillsDisableRequest(name: 'skill-name'));
$session->rpc()->skills()->reload();
$session->rpc()->skills()->ensureLoaded(); // 未読込のスキルを読み込む（再読込はしない）
$invoked = $session->rpc()->skills()->getInvoked();
// $invoked->skills - セッション中に呼び出されたスキルの履歴
```

## autopilotObjective

`autopilotObjective()->getState()`はセッションの現在のAutopilot objective状態を型付きで返します。objectiveが存在しない場合、`state`は`null`です。Workspaceに保存されたobjectiveの有無を確認する`workspaces()->autopilotObjectiveExists()`とは異なるAPIです。

```php
$state = $session->rpc()->autopilotObjective()->getState()->state;

if ($state !== null) {
    dump($state->objective, $state->status, $state->turnCount, $state->creditLimit);
}
```

`setStayInAutopilot()`でobjectiveの完了・一時停止時にinteractive modeへ戻る要求を抑制できます。この設定はクライアントのメモリ内に保持され、既定値は`true`です。`value`に`false`を渡すと抑制を解除できます。接続し直した場合は再設定してください。

```php
$session->rpc()->autopilotObjective()->setStayInAutopilot(['value' => true]);
```
