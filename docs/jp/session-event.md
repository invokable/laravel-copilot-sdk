# SessionEvent

Copilotからのメッセージは全て `Revolution\Copilot\Types\SessionEvent` クラス。最も使うことになるクラスなのでLaravel流の使い方ができるようにいろいろ追加している。

## content()

一番重要なAIからの応答メッセージを取得する。

```php
$response = Copilot::run('1 + 1');
echo $response->content(); // '2'
// content()はnullなこともある
```

`__toString()`により強制的に型変換が行われた場合もメッセージ内容が返される。

```php
echo (string) $response; // '2'
// nullにはならない。
```

## EventTypeの判定

`isAssistantMessage()`, `isUserMessage()`, `isIdle()`は用意。他にもよく使うものがあれば追加。

`is()`で任意のEventTypeを判定できる。

```php
if ($response->is(SessionEventType::HOOK_START)) {
    // フック開始イベントの場合の処理
}
```

`type()`はSessionEventType enumの値のstringを返す。

```php
echo $response->type()// 'assistant.message'
```

## failed() / successful()

`SESSION_ERROR`イベントタイプの場合にfailed。successfulは反対。  
元は`isError()`だったけどLaravel流に変更。  
LaravelのHttpやProcessとは違いSessionEventが届く前に例外が発生した場合は例外がスローされる。

## Conditionable

`when()`や`unless()`メソッドが使える。

```php
$response->when($response->isAssistantMessage(), function (SessionEvent $event) {
    // アシスタントメッセージの場合の処理
});
```

## Dumpable

`dump()`や`dd()`メソッドが使える。

```php
$response->dump();
```

## Tappable

`tap()`メソッドが使える。Laravelの中でも使い所が分かりにくいと言われる機能No.1。

```php
return $response->tap(function (SessionEvent $event) {
    // 何か処理
    info($event->content());
});
```

元の$responseには一切影響を与えず何かの処理を行えるのがtap()。

## InteractsWithData

これだけはSessionEventの`$data`プロパティに対する機能なので注意。  

`all()`, `has()`, `only()`, `collect()`などよく見るメソッドが揃っている。  
https://github.com/laravel/framework/blob/12.x/src/Illuminate/Support/Traits/InteractsWithData.php

SessionEventはEventTypeによって`$data`プロパティの中身が異なるので、EventTypeに応じたデータアクセスを行う場合に便利。

`content()`も実際にはInteractsWithDataのメソッドを使用している。
```php
return $this->data('content', $default);

// よってdefaultを指定すればnullにはならない
echo $response->content('');
```

## toArray() / toJson()

SessionEvent全体を配列やJSONに変換できる。

```php
$array = $response->toArray();
$json = $response->toJson();
```

`collect()`も用意しようとしたけどInteractsWithDataにあるので全体版はなし。必要な場合は通常の`collect()`ヘルパーを使う。
```php
$collect = collect($response->toArray());
```
