# モデルリスト

`Copilot::client()->listModels()`でCopilot CLIがサポートしているモデルの一覧を取得できます。

利用可能なモデルはCopilot CLIのアップデートや組織のポリシーで変わります。
[モデルリスト](../models-list.md)は参考程度にしてください。

## モデルIDの指定

SessionConfigの`model`に指定するのは`ID`。

```php
Copilot::run($prompt, config: ['model' => 'auto']);
```
