# SessionEvent

Copilotからのメッセージはすべて `Revolution\Copilot\Types\SessionEvent` クラスです。最も使うことになるクラスなのでLaravel流の使い方ができるようにいろいろ追加しています。

## content()

一番重要なAIからの応答メッセージを取得します。

```php
$response = Copilot::run('1 + 1');
echo $response->content(); // '2'
// content()はnullなこともある
```

`__toString()`により強制的に型変換が行われた場合もメッセージ内容が返されます。

```php
echo (string) $response; // '2'
// nullにはならない。
```

## EventTypeの判定

`isAssistantMessage()`, `isUserMessage()`, `isIdle()`、`isAssistantMessageDelta()`は用意しています。他にもよく使うものがあれば追加します。

`is()`で任意のEventTypeを判定できます。

```php
if ($response->is(SessionEventType::HOOK_START)) {
    // フック開始イベントの場合の処理
}
```

`type()`はSessionEventType enumの値のstringを返します。

```php
echo $response->type()// 'assistant.message'
```

## failed() / successful()

`SESSION_ERROR`イベントタイプの場合にfailedになります。successfulは反対です。  
元は`isError()`でしたがLaravel流に変更しました。  

## throw()

LaravelのHttpやProcess同様に例外が発生するエラーでも保留しておき`throw()`メソッドで例外をスローします。エラーではない時は何もしないので以下のように書けます。

```php
$content = $response->throw()->content();
```

`SESSION_ERROR`イベントなら`Revolution\Copilot\Exceptions\SessionErrorException`, タイムアウト時は`Revolution\Copilot\Exceptions\SessionTimeoutException`がスローされる。

JSON-RPCのエラーでは`Revolution\Copilot\Exceptions\JsonRpcException`。

## Conditionable

`when()`や`unless()`メソッドが使えます。

```php
$response->when($response->isAssistantMessage(), function (SessionEvent $event) {
    // アシスタントメッセージの場合の処理
});
```

## Dumpable

`dump()`や`dd()`メソッドが使えます。

```php
$response->dump();
```

## Tappable

`tap()`メソッドが使えます。

```php
return $response->tap(function (SessionEvent $event) {
    // 何か処理
    info($event->content());
});
```

## InteractsWithData

これだけはSessionEventの`$data`プロパティに対する機能なので注意してください。  

`all()`, `has()`, `only()`, `collect()`などよく見るメソッドが揃っています。  
https://github.com/laravel/framework/blob/12.x/src/Illuminate/Support/Traits/InteractsWithData.php

SessionEventはEventTypeによって`$data`プロパティの中身が異なるので、EventTypeに応じたデータアクセスを行う場合に便利です。

`content()`も実際にはInteractsWithDataのメソッドを使用しています。
```php
return $this->data('content', $default);

// よってdefaultを指定すればnullにはならない
echo $response->content('');
```

## toArray() / toJson()

SessionEvent全体を配列やJSONに変換できます。

```php
$array = $response->toArray();
$json = $response->toJson();
```

`collect()`も用意しようとしましたが、InteractsWithDataにあるので全体版はありません。必要な場合は通常の`collect()`ヘルパーを使います。
```php
$collect = collect($response->toArray());
```
