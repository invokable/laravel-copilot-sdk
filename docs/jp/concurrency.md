# Laravel の Concurrency を使った並行実行

## stdioモードの場合

それぞれでプロセスを起動するのは無駄ですがこの書き方でしか動きませんでした。Concurrency内部では複雑なシリアライズをしているのでSessionEventクラスではなくcontent()の文字列だけを返します。

```php
use Revolution\Copilot\Facades\Copilot;
use Illuminate\Support\Facades\Concurrency;

$prompt = 'Tell me something about Copilot.';

[$gpt5_response, $sonnet_response] = Concurrency::run([
    fn () => Copilot::run($prompt, config: ['model' => 'gpt-5.2'])->content(),
    fn () => Copilot::run($prompt, config: ['model' => 'claude-sonnet-4.5'])->content(),
]);

echo 'GPT-5 Response: '.$gpt5_response;
echo 'Claude Sonnet Response: '.$sonnet_response;
```

## TCPモードの場合

プロセスの起動がない分早そうですが実際にはあまり変わりませんでした。

Processドライバーではどちらでも60秒でタイムアウトになることが多いです。CLI環境専用`fork`ドライバーでは片方だけ成功しました。例外で全部失敗にはなりません。`gpt-5`だけ失敗していたので`gpt-5.2`に変えたら両方成功しました。単純にモデルの応答時間の問題だったのかもしれません。

Concurrencyは機能としては存在しますが実用できるかは不明です。時間がかかる処理はキューを使うのが基本です。
