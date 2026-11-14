# Plugins・Extensions

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

```php
// plugins (experimental: プラグインの一覧)
$session->rpc()->plugins()->list();
// 同名の直接インストールを指定する場合、list() の directSourceId を names に渡す
$session->rpc()->plugins()->enable(['names' => ['opaque-direct-source-id']]);
$session->rpc()->plugins()->disable(['names' => ['opaque-direct-source-id']]);
// プラグインをリロード
$session->rpc()->plugins()->reload();
// オプションを指定してリロード
use Revolution\Copilot\Types\Rpc\PluginsReloadRequest;

$session->rpc()->plugins()->reload(new PluginsReloadRequest(
    reloadMcp: true,      // MCPサーバー設定を再読み込み
    reloadHooks: true,    // フックを再読み込み
    reloadCustomAgents: false, // カスタムエージェントを再読み込みしない
));
// セッションのソフトなプラグイン既定値を取得（設定の永続化やインストールは行わない）
$defaults = $session->rpc()->plugins()->getDefaults(); // SessionPluginDefaults
// $defaults->defaultEnabledPlugins はマネージド既定値がTeam既定値を上書きした結果
// $defaults->teamDefaultEnabledPlugins はTeamのみの既定値
// $defaults->extraKnownMarketplaces はインストール時のフォールバック

// extensions (experimental: エクステンションの管理)
$policy = $session->rpc()->extensions()->getPolicy(); // ExtensionManagedPolicy
// $policy->standalone は allowed / plugin_only / pending
// ポリシーはスナップショットであり、起動許可のトークンではありません。実際の起動判断には使わないでください
$session->rpc()->extensions()->list();
$session->rpc()->extensions()->enable(new ExtensionsEnableRequest(id: 'project:my-ext'));
$session->rpc()->extensions()->disable(new ExtensionsDisableRequest(id: 'project:my-ext'));
$session->rpc()->extensions()->reconcile(); // host-only: 実効membershipを再同期し、settled listを返す
$session->rpc()->extensions()->reload();
```
