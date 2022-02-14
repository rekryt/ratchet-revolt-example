# Non-blocking HTTP\WS Server (with PHP 8.1 fibers)
![ratchet-revolt-example](http://rekryt.ru/files/ratchet-revolt-example.png)

### What is Revolt?

Revolt is a rock-solid event loop for concurrent PHP applications.

-   https://revolt.run/
-   https://github.com/revoltphp/event-loop

### What is Ratchet?

Ratchet is a loosely coupled PHP library providing developers with tools to create real time, bi-directional applications between clients and servers over WebSockets.

-   http://socketo.me/
-   https://github.com/ratchetphp/Ratchet

### What is ReactPHP event loop?

Ratchet based on ReactPHP event loop but to use native fibers, the loop can be replaced to Revolt event loop by ReactAdaptor

-   https://github.com/amphp/react-adapter
-   https://github.com/Rekryt/react-adapter - for Amphp v3

### What is Amphp?

AMPHP is a collection of event-driven libraries for PHP designed with fibers and concurrency in mind.

-   https://github.com/amphp/amp/commits/v3 - Amphp v3

## Usage

```shell
docker-compose up -d
```

HTTP server starts at: http://`docker-machine ip`:80/

WS server starts at: ws://`docker-machine ip`:81/

## Usage (docker)

```shell
docker build -t ratchet_revolt .
docker run -it -p 80:80 -p 81:81 ratchet_revolt
```

## Usage (php)

```
composer install
php index.php
```

# Example

/index.php

```php
<?php

$host = isset($_ENV['HOST']) ? $_ENV['HOST'] : '0.0.0.0';
$port = isset($_ENV['PORT']) ? $_ENV['PORT'] : 80;
$WSport = isset($_ENV['WS_PORT']) ? $_ENV['WS_PORT'] : 81;

// Get react Loop over amphp Loop
$loop = ReactAdapter::get();

// Create socket and http server
$socket = new SocketServer($host . ':' . $port, [], $loop);
$http = new HttpServer(new HTTPExampleServer());
// Add HTTP server to loop
new IoServer($http, $socket, $loop);
echo 'Listening on: ' . $host . ':' . $port . PHP_EOL;

// Create socket and ws server
$ws_socket = new SocketServer($host . ':' . $WSport, [], $loop);
$ws = new HttpServer(new WsServer(new WSExampleServer()));
// Add WS server to loop
new IoServer($ws, $ws_socket, $loop);
echo 'Listening on: ' . $host . ':' . $WSport . PHP_EOL;

$loop->addSignal(SIGTERM, function () use ($loop) {
    $loop->stop(); // Gracefully stopping
});

// Start
$loop->run();
```

/src/HTTPExampleServer.php

```php
<?php
use Exception;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;

class HTTPExampleServer implements HttpServerInterface {
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        $contentType = 'application/json; charset=utf-8'; // or 'text/plain'
        if ($request->getUri()->getPath() == '/api') {
            $body = rand(0, 100);
        } else {
            $contentType = 'text/html; charset=UTF-8';
            $body = file_get_contents(__DIR__ . '/static/index.html');
        }

        $e = "\r\n";
        $headers = [
            'HTTP/1.1 200 OK',
            'Date: ' . date('D') . ', ' . date('m') . ' ' . date('M') . ' ' . date('Y') . ' ' . date('H:i:s') . ' GMT',
            'Server: ExampleServer',
            'Connection: close',
            'Content-Type: ' . $contentType,
            'Content-Length: ' . strlen($body),
        ];

        $headers = implode($e, $headers) . $e . $e;

        $conn->send($headers . $body);
        $conn->close();
    }
...
}
```

/src/WSExampleServer.php

```php
<?php
use Exception;
use Psr\Http\Message\ResponseInterface;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use function Amp\async;

class WSExampleServer implements MessageComponentInterface {
    public function onMessage(ConnectionInterface $conn, MessageInterface $msg) {
        $task = new WSExampleTask();
        $options = json_decode($msg);

        for ($i = 0; $i < $options->count; $i++) {
            $time = microtime(true);
            //$closure = static function () use ($conn, $task, $time, $i) {
            $closure = fn() => $task->execute()->then(function (ResponseInterface $response) use ($conn, $time, $i) {
                $res = [
                    'id' => $i,
                    'rand' => (string) $response->getBody(),
                    'time' => (microtime(true) - $time) * 1000,
                    'memory' => memory_get_usage(),
                ];
                $conn->send(json_encode($res));
            });
            if ($options->async) {
                // async call with concurrent fibers
                async($closure);
            } else {
                // synchronous call
                $closure();
            }
        }
    }
...
}
```

/src/WSExampleTask.php

```php
<?php
use Amp\ReactAdapter\ReactAdapter;
use React\Http\Browser;
use React\Promise\PromiseInterface;

class WSExampleTask {
    public function execute(): PromiseInterface {
        $client = new Browser(null, ReactAdapter::get());
        return $client->get('http://127.0.0.1/api');
    }
}
```

/src/static/index.html
```javascript
let ws, timer;
new Vue({
    el: '#app',
    vuetify: new Vuetify(),
    ...data,
    ...computed,
    mounted() {
        ws = new WebSocket('ws://' + document.location.hostname + ':81');
        ws.onopen = this.onOpen;
        ws.onmessage = this.onMessage;
        timer = setInterval(this.doProcess, this.timeout);
        setInterval(this.doMonitor, this.timeoutMonitor);
    },
    methods: {
        send(msg) {
            ws.send(JSON.stringify(msg));
        },
        doProcess() {
            if (this.isProcess) {
                this.send({ async: this.async, count: this.count });
                this.sendedRequests = this.sendedRequests + 1;
                this.sendedTasks = this.sendedTasks + this.count;
            }
        },
        ...methods,
    },
    ...
});
```

### Other links

-   https://github.com/amphp/http-client - HTTP client
-   http://socketo.me/docs/http - Ratchet HTTP\WS server
-   https://vuetifyjs.com/ - Vuetify JS
-   https://wiki.php.net/rfc/fibers - PHP RFC
