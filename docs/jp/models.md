# モデルリスト

`Copilot::client()->listModels()`でCopilot CLIがサポートしているモデルの一覧を取得できる。

## キャッシュ

モデルリストは最初の呼び出し後にメモリ内にキャッシュされ、レート制限を回避します。キャッシュはクライアントの切断時（`stop()`メソッド呼び出し時）にクリアされます。

これは公式SDKの[PR #300](https://github.com/github/copilot-sdk/pull/300)で導入された機能で、並行処理下でのレート制限エラーを防ぐために実装されています。

## モデルIDの指定

SessionConfigの`model`に指定するのは`ID`。

```php
Copilot::run($prompt, config: ['model' => 'claude-opus-4.5']);
```

```plaintext
 ┌──────────────────────┬────────────────────────┬────────────────────┬────────────────┬───────────────────────────┐
 │ ID                   │ Display Name           │ Max Context Tokens │ Vision Support │ Supports Reasoning Effort │
 ├──────────────────────┼────────────────────────┼────────────────────┼────────────────┼───────────────────────────┤
 │ claude-sonnet-4.5    │ Claude Sonnet 4.5      │ 144000             │ Yes            │ No                        │
 │ claude-haiku-4.5     │ Claude Haiku 4.5       │ 144000             │ Yes            │ No                        │
 │ claude-opus-4.5      │ Claude Opus 4.5        │ 160000             │ Yes            │ No                        │
 │ claude-sonnet-4      │ Claude Sonnet 4        │ 216000             │ Yes            │ No                        │
 │ gemini-3-pro-preview │ Gemini 3 Pro (Preview) │ 128000             │ Yes            │ No                        │
 │ gpt-5.2-codex        │ GPT-5.2-Codex          │ 400000             │ Yes            │ Yes                       │
 │ gpt-5.2              │ GPT-5.2                │ 264000             │ Yes            │ Yes                       │
 │ gpt-5.1-codex-max    │ GPT-5.1-Codex-Max      │ 400000             │ Yes            │ Yes                       │
 │ gpt-5.1-codex        │ GPT-5.1-Codex          │ 400000             │ Yes            │ Yes                       │
 │ gpt-5.1              │ GPT-5.1                │ 264000             │ Yes            │ Yes                       │
 │ gpt-5                │ GPT-5                  │ 400000             │ Yes            │ Yes                       │
 │ gpt-5.1-codex-mini   │ GPT-5.1-Codex-Mini     │ 400000             │ Yes            │ Yes                       │
 │ gpt-5-mini           │ GPT-5 mini             │ 264000             │ Yes            │ Yes                       │
 │ gpt-4.1              │ GPT-4.1                │ 128000             │ Yes            │ No                        │
 └──────────────────────┴────────────────────────┴────────────────────┴────────────────┴───────────────────────────┘
```
