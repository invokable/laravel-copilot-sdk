<html>
<body>

<script>
    const source = new EventSource('/copilot/stream');

    source.addEventListener('update', (event) => {
        if (event.data === '</stream>') {
            source.close();

            return;
        }

        console.log(event.data);
        document.getElementById("output").innerHTML += event.data;
    });
</script>

<h1>Copilot SSE Test</h1>
<div id="output"></div>
</body>
</html>
