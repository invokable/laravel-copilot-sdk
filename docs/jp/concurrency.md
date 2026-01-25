# Laravel の Concurrency を使った並行実行

## stdioモードの場合

それぞれでプロセスを起動するのは無駄だけどこの書き方でしか動かなかった。Concurrency内部では複雑なシリアライズしてるのでSessionEventクラスではなくcontent()の文字列だけを返す。

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

プロセスの起動がない分早そうだけど実際にはあまり変わらなかった。

Processドライバーではどっちでも60秒でタイムタウトになることが多い。CLI環境専用`fork`ドライバーでは片方だけ成功。例外で全部失敗にはならない。`gpt-5`だけ失敗していたので`gpt-5.2`に変えたら両方成功。単純にモデルの応答時間の問題だったのかも。

Concurrencyは機能としては存在するけど実用できるかは不明。時間がかかる処理はキューを使うのが基本。
