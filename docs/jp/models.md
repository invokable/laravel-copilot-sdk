# モデルリスト

`Copilot::client()->listModels()`でCopilot CLIがサポートしているモデルの一覧を取得できます。

## モデルIDの指定

SessionConfigの`model`に指定するのは`ID`。

```php
Copilot::run($prompt, config: ['model' => 'auto']);
```
