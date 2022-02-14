<?php
/**
 * RevoltPHP Ratchet ReactPHP Fibers (PHP8.1) async example
 *
 * @package RatchetRevolt
 * @author Krupkin Sergey <rekrytkw@gmail.com>
 */
namespace RatchetRevoltExample;

// Load dependencies
require_once 'vendor/autoload.php';

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\SocketServer;
use Amp\ReactAdapter\ReactAdapter;

// ini_set('memory_limit', '512M');

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
echo PHP_EOL;
$loop->run();
