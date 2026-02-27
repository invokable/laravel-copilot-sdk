# RPC

公式SDKでは最近の新機能は`api.schema.json`を元にした自動コード生成で対応しています。Laravel版では生成後の他言語版を参考に同じように実装しています。

## ServerRpc

Clientに紐づくRPCクラス。

```php
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\ModelsListResult;

// モデルリストの取得
// 返り値はModelsListResult
$result = Copilot::client()->rpc()->models()->list();
// modelsはModelInfoの配列
$models = $result->models;

// 初期からあるlistModels()とほとんど同じ
// 返り値はModelInfoの配列
Copilot::client()->listModels();
```

元からSDKに含まれてた機能も自動コード生成版で再度追加されています。

### メソッドリスト

```php
Copilot::client()->rpc()->ping();

// models
Copilot::client()->rpc()->models()->list();

// tools
Copilot::client()->rpc()->tools()->list();

// account
Copilot::client()->rpc()->account()->getQuota();
```

## SessionRpc

Sessionに紐づくRPCクラス。

以前のSDKではできなかったプランモードの利用なども可能です。

```php
use Revolution\Copilot\Contracts\CopilotSession;
use Revolution\Copilot\Facades\Copilot;
use Revolution\Copilot\Types\Rpc\SessionModeSetParams;
use Revolution\Copilot\Types\Rpc\SessionPlanReadResult;

Copilot::start(function (CopilotSession $session) {
    $session->rpc()->mode()->set(new SessionModeSetParams(mode: 'plan'));

    $response = $session->sendAndWait(prompt: '〇〇のプランを作成');

    $result = $session->rpc()->plan()->read();
    dump($result->content);

    $session->rpc()->mode()->set(new SessionModeSetParams(mode: 'autopilot'));

    $response = $session->sendAndWait(prompt: 'プランに従って実装');
    dump($response->content());
});
```

### メソッドリスト

```php
// model
$session->rpc()->model()->getCurrent();
$session->rpc()->model()->switchTo(new SessionModelSwitchToParams(modelId: 'gpt-4'));

// mode
$session->rpc()->mode()->get();
$session->rpc()->mode()->set(new SessionModeSetParams(mode: 'plan'));

// plan
$session->rpc()->plan()->read();
$session->rpc()->plan()->update(new SessionPlanUpdateParams(content: '...'));
$session->rpc()->plan()->delete();

// workspace
$session->rpc()->workspace()->listFiles();
$session->rpc()->workspace()->readFile(new SessionWorkspaceReadFileParams(path: 'file.txt'));
$session->rpc()->workspace()->createFile(new SessionWorkspaceCreateFileParams(path: 'file.txt', content: '...'));

// fleet
$session->rpc()->fleet()->start();

// agent
$session->rpc()->agent()->list();
$session->rpc()->agent()->getCurrent();
$session->rpc()->agent()->select(new SessionAgentSelectParams(agentId: '...'));
$session->rpc()->agent()->deselect();

// compaction
$session->rpc()->compaction()->compact();
```
