<?php
namespace RatchetRevoltExample;

use Exception;
use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;

/**
 * Class HTTPExampleServer
 * @package RatchetRevoltExample
 */
class HTTPExampleServer implements HttpServerInterface {
    /**
     * @param ConnectionInterface $conn
     * @param RequestInterface|null $request
     */
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

    /**
     * @param ConnectionInterface $conn
     */
    function onClose(ConnectionInterface $conn) {
        // TODO: Implement onClose() method.
    }

    /**
     * @param ConnectionInterface $conn
     * @param Exception $e
     */
    function onError(ConnectionInterface $conn, Exception $e) {
        // TODO: Implement onError() method.
    }

    /**
     * @param ConnectionInterface $from
     * @param string $msg
     */
    function onMessage(ConnectionInterface $from, $msg) {
        // TODO: Implement onMessage() method.
    }
}
