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
     * @var string
     */
    private string $html;

    /**
     * HTTPExampleServer constructor.
     */
    public function __construct() {
        $this->html = file_get_contents(__DIR__ . '/static/index.html');
        foreach ($_ENV as $key => $value) {
            $this->html = str_replace('{' . $key . '}', $value, $this->html);
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param RequestInterface|null $request
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null) {
        $contentType = 'application/json; charset=utf-8'; // or 'text/plain'
        if ($request->getUri()->getPath() == '/api') {
            $body = rand(0, 100);
        } elseif ($request->getUri()->getPath() == '/info') {
            $body = json_encode([
                'extensions' => get_loaded_extensions(),
                'driver' => isset($_ENV['REVOLT_DRIVER']) ? $_ENV['REVOLT_DRIVER'] : 'default',
            ]);
        } else {
            $contentType = 'text/html; charset=UTF-8';
            $body = $this->html;
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
