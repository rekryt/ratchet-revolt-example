<?php
/**
 * Ratchet PHP8 async example
 *
 * @package RatchetPrometheusExporter
 * @author Krupkin Sergey <rekrytkw@gmail.com>
 */
namespace Example;

// Load dependencies
require_once 'vendor/autoload.php';

use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\Socket\SocketServer;
use Revolt\EventLoop;
use Amp\ReactAdapter\ReactAdapter;
//use React\EventLoop\Loop;

$host = isset($_ENV['HOST']) ? $_ENV['HOST'] : '0.0.0.0';
$port = isset($_ENV['PORT']) ? $_ENV['PORT'] : 80;
$WSport = isset($_ENV['WS_PORT']) ? $_ENV['WS_PORT'] : 81;


// Get react Loop
//$loop = Loop::get();
$loop = ReactAdapter::get();

// Create socket and http server
$socket = new SocketServer($host . ':' . $port, [], $loop);
$http = new HttpServer(new HTTPExampleServer());
// Add HTTP server to loop
new IoServer($http, $socket, $loop);
echo 'Listening on: ' . $host . ':' . $port . PHP_EOL;

// Create ws server
$ws_socket = new SocketServer($host . ':' . $WSport, [], $loop);
$ws = new HttpServer(new WsServer(new WSExampleServer()));
// Add WS server to loop
new IoServer($ws, $ws_socket, $loop);
echo 'Listening on: ' . $host . ':' . $WSport . PHP_EOL;

// Start
$loop->run();
