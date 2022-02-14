<?php

namespace RatchetRevoltExample;

use Psr\Http\Message\ResponseInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use React\Http\Browser;
use React\Socket\SocketServer;
use function Amp\async;
use function Amp\delay;

class SmokeTest extends RatchetRevoltExampleTestCase {
    public function testHTTPServer() {
        // Add HTTP server to loop
        new IoServer(
            new HttpServer(new HTTPExampleServer()),
            new SocketServer('0.0.0.0:8080', [], $this->loop),
            $this->loop
        );

        // Async test request for get random integer
        async(function (): void {
            $client = new Browser(null, $this->loop);
            $client
                ->get('http://127.0.0.1:8080/api')
                ->then(function (ResponseInterface $response) {
                    /** @see WSExampleTask **/
                    $this->assertIsNumeric((string) $response->getBody());
                })
                ->then(function () {
                    $this->loop->stop();
                });
        });

        $this->loop->run();
    }
}
