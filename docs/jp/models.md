# モデルリスト

`Copilot::client()->listModels()`でCopilot CLIがサポートしているモデルの一覧を取得できる。

## キャッシュ

モデルリストは最初の呼び出し後にメモリ内にキャッシュされ、レート制限を回避します。キャッシュはクライアントの切断時（`stop()`メソッド呼び出し時）にクリアされます。

これは公式SDKの[PR #300](https://github.com/github/copilot-sdk/pull/300)で導入された機能で、並行処理下でのレート制限エラーを防ぐために実装されています。

## モデルIDの指定

SessionConfigの`model`に指定するのは`ID`の方。

```php
Copilot::run($prompt, config: ['model' => 'claude-opus-4.5']);
```

```plaintext
 ┌──────────────────────┬────────────────────────┐
 │ ID                   │ Display Name           │
 ├──────────────────────┼────────────────────────┤
 │ claude-sonnet-4.5    │ Claude Sonnet 4.5      │
 │ claude-haiku-4.5     │ Claude Haiku 4.5       │
 │ claude-opus-4.5      │ Claude Opus 4.5        │
 │ claude-sonnet-4      │ Claude Sonnet 4        │
 │ gemini-3-pro-preview │ Gemini 3 Pro (Preview) │
 │ gpt-5.2-codex        │ GPT-5.2-Codex          │
 │ gpt-5.2              │ GPT-5.2                │
 │ gpt-5.1-codex-max    │ GPT-5.1-Codex-Max      │
 │ gpt-5.1-codex        │ GPT-5.1-Codex          │
 │ gpt-5.1              │ GPT-5.1                │
 │ gpt-5                │ GPT-5                  │
 │ gpt-5.1-codex-mini   │ GPT-5.1-Codex-Mini     │
 │ gpt-5-mini           │ GPT-5 mini             │
 │ gpt-4.1              │ GPT-4.1                │
 └──────────────────────┴────────────────────────┘

```
