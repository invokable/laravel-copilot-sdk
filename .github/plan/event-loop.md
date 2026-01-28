# EventLoop

PHPは同期しかできないと思われてるけど実際には非同期機能の実装が徐々に進んでいる。

True Asyncの実装が最終目標。
https://wiki.php.net/rfc/true_async

現状ではwhileループ部分だけEventLoopを使うのが良さそう。
ReactPHPとかもあるけどここでは他の自作パッケージでも使っているrevolt/event-loopを使う。
revolt/event-loopなら今のstream_selectのほかPHP拡張をインストールすれば自動的に他の効率的な実装に切り替わる。
https://revolt.run/
https://github.com/revoltphp/event-loop

多分こんなコードに書き換えられる。タイムアウトはEventLoop::delayでfloatで秒単位なので今と同じ。

```php
use Revolt\EventLoop;

stream_set_blocking(STDIN, false)

$suspension = EventLoop::getSuspension();

$readableId = EventLoop::onReadable(STDIN, function ($id, $stream) use ($suspension): void
{

});

$timeoutId = EventLoop::delay(60.0, fn() => $suspension->resume());

$suspension->suspend();

EventLoop::cancel($readableId);
EventLoop::cancel($timeoutId);
```

## 作業
- whileループを使っている箇所を検索して変更できそうならEventLoopに書き換える
- 必要ならテストを修正
- テストが成功したら完了。

## 注意点
- EventLoopを使うのは内部コードのみ。ユーザーには意識させない。
- Transportの「Switch to blocking mode for reliable reads」は初期にblockingにしないと動作しなかった名残なのでEventLoopで不要ならblockingに戻す部分は削除。
