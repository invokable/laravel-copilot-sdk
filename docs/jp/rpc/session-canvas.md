# Session canvas

対象: SessionRpc（Session に紐づくセッション単位の操作）

RPC 全体の概要とページ一覧は [RPC](../rpc.md) を参照してください。

## Session canvas

Canvas RPC は `$session->rpc()->canvas()` から呼び出します。`list()` は
`CanvasList` の `canvases`、`listOpen()` は `CanvasListOpenResult` の
`openCanvases` に一覧を返します。`open()` は `OpenCanvasInstance`、
action 呼び出しは `CanvasActionInvokeResult` を返します。`close()` の
戻り値はありません。

```php
$canvases = $session->rpc()->canvas();
$available = $canvases->list()->canvases;
$openInstances = $canvases->listOpen()->openCanvases;

$instance = $canvases->open([
    'canvasId' => 'editor',
    'instanceId' => 'editor-1',
    'input' => ['file' => 'README.md'],
]);

$result = $canvases->action()->invoke([
    'instanceId' => $instance->instanceId,
    'actionName' => 'format',
    'input' => ['style' => 'compact'],
]);

$canvases->close(['instanceId' => $instance->instanceId]);
```

Canvas provider の reverse-RPC (`canvas.open` / `canvas.close` /
`canvas.action.invoke`) は `SessionConfig` / `ResumeSessionConfig` の
`canvasHandler` で処理します。Canvas の描画はホスト UI の責務です。
