# モデルリスト

`Copilot::client()->listModels()`でCopilot CLIがサポートしているモデルの一覧を取得できます。

## モデルIDの指定

SessionConfigの`model`に指定するのは`ID`。

```php
Copilot::run($prompt, config: ['model' => 'claude-opus-4.7']);
```

```plaintext
 ┌───────────────────┬───────────────────┬────────────────────┬────────────────┬───────────────────────────┬─────────────────────────────┐
 │ ID                │ Display Name      │ Max Context Tokens │ Vision Support │ Supports Reasoning Effort │ Supports Structured Outputs │
 ├───────────────────┼───────────────────┼────────────────────┼────────────────┼───────────────────────────┼─────────────────────────────┤
 │ auto              │ Auto              │ 0                  │ No             │ No                        │ No                          │
 │ claude-sonnet-4.6 │ Claude Sonnet 4.6 │ 200000             │ Yes            │ Yes                       │ Yes                         │
 │ claude-sonnet-4.5 │ Claude Sonnet 4.5 │ 144000             │ Yes            │ No                        │ No                          │
 │ claude-haiku-4.5  │ Claude Haiku 4.5  │ 144000             │ Yes            │ No                        │ No                          │
 │ claude-opus-4.7   │ Claude Opus 4.7   │ 144000             │ Yes            │ Yes                       │ Yes                         │
 │ claude-opus-4.6   │ Claude Opus 4.6   │ 144000             │ Yes            │ Yes                       │ Yes                         │
 │ claude-opus-4.5   │ Claude Opus 4.5   │ 160000             │ Yes            │ No                        │ No                          │
 │ claude-sonnet-4   │ Claude Sonnet 4   │ 216000             │ Yes            │ No                        │ No                          │
 │ gpt-5.4           │ GPT-5.4           │ 400000             │ Yes            │ Yes                       │ Yes                         │
 │ gpt-5.3-codex     │ GPT-5.3-Codex     │ 400000             │ Yes            │ Yes                       │ Yes                         │
 │ gpt-5.2-codex     │ GPT-5.2-Codex     │ 400000             │ Yes            │ Yes                       │ Yes                         │
 │ gpt-5.2           │ GPT-5.2           │ 264000             │ Yes            │ Yes                       │ Yes                         │
 │ gpt-5.4-mini      │ GPT-5.4 mini      │ 400000             │ Yes            │ Yes                       │ Yes                         │
 │ gpt-5-mini        │ GPT-5 mini        │ 264000             │ Yes            │ Yes                       │ Yes                         │
 │ gpt-4.1           │ GPT-4.1           │ 128000             │ Yes            │ No                        │ Yes                         │
 └───────────────────┴───────────────────┴────────────────────┴────────────────┴───────────────────────────┴─────────────────────────────┘
```
