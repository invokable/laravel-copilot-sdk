# RPC(Remote Procedure Call)

公式SDKでは最近の新機能は`api.schema.json`を元にした自動コード生成で対応しています。Laravel版では生成後の他言語版を参考に同じように実装しています。

## ServerRpc

Clientに紐づくRPCクラス。

カテゴリー別のメソッド概要は [ServerRpc 概要](rpc/server-overview.md) にまとめています。

### 基本的な呼び出し例

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Enums\TokenPriceUnit;
use Revolution\Copilot\Types\Rpc\ModelsListTokenPriceUnitsRequest;
use Revolution\Copilot\Types\Rpc\ModelList;

// モデルリストの取得
// 返り値はModelList
$result = Copilot::client()->rpc()->models()->list();
// modelsはModelInfoの配列
$models = $result->models;

// 初期からあるlistModels()とほとんど同じ
// 返り値はModelInfoの配列
Copilot::client()->listModels();
```

元からSDKに含まれてた機能も自動コード生成版で再度追加されています。

### 使用例（機能別ページ）

代表的な呼び出し例は機能別のページにまとめています。

- [モデル・ツール・アカウント](rpc/server-models.md)
- [ローカルモデル（Azure ML）](rpc/server-local-models.md)
- [MCP・コネクター](rpc/server-mcp.md)
- [設定・管理設定](rpc/server-settings.md)
- [HTTP推論・ファイルシステム](rpc/server-inference.md)
- [セッション・ホスト・環境](rpc/server-sessions.md)
- [Skills・Plugins・Agents・Instructions・Extensions](rpc/server-skills-plugins.md)
- [Git・GitHub](rpc/server-git.md)

## SessionRpc

Sessionに紐づくRPCクラス。

以前のSDKではできなかったプランモードの利用なども可能です。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\ModeSetRequest;
use Revolution\Copilot\Types\Rpc\PlanReadResult;

Copilot::start(function (CopilotSession $session) {
    $session->rpc()->mode()->set(new ModeSetRequest(mode: 'plan'));

    $response = $session->sendAndWait(prompt: '〇〇のプランを作成');

    $result = $session->rpc()->plan()->read();
    dump($result->content);

    $session->rpc()->mode()->set(new ModeSetRequest(mode: 'autopilot'));

    $response = $session->sendAndWait(prompt: 'プランに従って実装');
    dump($response->content());
});
```

カテゴリー別のメソッド概要は [SessionRpc 概要](rpc/session-overview.md) にまとめています。

### 使用例（機能別ページ）

代表的な呼び出し例は機能別のページにまとめています。

- [モデル・モード・名前・プラン](rpc/session-model.md)
- [ワークスペース・Agent・Fleet・Skills](rpc/session-workspace.md)
- [MCP](rpc/session-mcp.md)
- [Plugins・Extensions](rpc/session-plugins.md)
- [管理設定・debug・履歴](rpc/session-settings.md)
- [Tools・Permissions・Commands・UI](rpc/session-tools.md)
- [ログ・シェル・使用量・テレメトリ・認証](rpc/session-activity.md)
- [プロバイダー（BYOK）](rpc/session-provider.md)
- [キュー・スケジュール・タスク](rpc/session-queue-tasks.md)
- [リモート・サンドボックス](rpc/session-remote.md)
- [セッション情報・ローカルセッション管理](rpc/session-info.md)
- [Session canvas](rpc/session-canvas.md)
- [Feature Flags・Workflow](rpc/session-workflow.md)
- [SessionFS コールバック](rpc/session-fs.md)

## 配列での引数指定

引数も返り値も専用のクラスを使っていますが引数は配列で指定することも可能です。

```php
$session->rpc()->mode()->set(['mode' => 'plan']);
```

## Testing

`Copilot::fake()`でのモックは使えないので`Copilot::expects('client')`や`Copilot::expects('start')`でモックしてください。
