# Context Board

対象: ServerRpc（Client に紐づくサーバー全体の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## contextBoard.getConsolidationPrompt（experimental）

`contextBoard()->getConsolidationPrompt()`は、セッションのコンテキストボードを統合するエージェント向けの、ランタイムが管理する指示プロンプトを返します。ホストが統合エージェントを起動する際に使用できます。

```php
use Revolution\Copilot\Facades\Copilot;

// contextBoard.getConsolidationPrompt
$result = Copilot::client()->rpc()->contextBoard()->getConsolidationPrompt();
$prompt = $result->prompt;
```

返り値は`ContextBoardGetConsolidationPromptResult`で、必須の文字列`prompt`を含みます。
