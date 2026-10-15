# モデルリスト

`Copilot::client()->listModels()`でCopilot CLIがサポートしているモデルの一覧を取得できます。

利用可能なモデルはCopilot CLIのアップデートや組織のポリシーで変わります。
[モデルリスト](../models-list.md)は参考程度にしてください。

## 画像モデルのカタログ

`listModels()`の戻り値にある`imageModels`は、ランタイムが選択した画像モデルのカタログです。会話用の`models`とは別の情報で、ここにある画像モデルをSessionConfigの`model`に指定することはできません。ランタイムがカタログを提供しない場合、`imageModels`は`null`です。

各画像モデルの`availability`は、解決されたアカウントでそのモデルを利用できるかを示します。これはセッションで画像生成が有効かどうかを示すものではありません。画像生成を使うには、セッションごとに`imageGeneration.enabled`を有効にする必要があり、利用時には別途パーミッションも適用されます。

## モデルIDの指定

SessionConfigの`model`に指定するのは`ID`。

```php
Copilot::run($prompt, config: ['model' => 'auto']);
```
