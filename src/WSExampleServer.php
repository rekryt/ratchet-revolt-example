<?php
namespace RatchetRevoltExample;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use Revolt\EventLoop;
use function Amp\async;
use function Amp\delay;
use function Amp\Future\await;

/**
 * Class WSExampleServer
 * @package RatchetRevoltExample
 */
class WSExampleServer implements MessageComponentInterface {
    /**
     * @param ConnectionInterface $conn
     * @param MessageInterface $msg
     */
    public function onMessage(ConnectionInterface $conn, MessageInterface $msg) {
        $task = new WSExampleTask();
        $options = json_decode($msg);

        for ($i = 0; $i < $options->count; $i++) {
            $time = microtime(true);
            $closure = static function () use ($conn, $task, $time, $i) {
                $task->execute()->then(function (ResponseInterface $response) use ($conn, $time, $i) {
                    $conn->send(json_encode([
                        'id' => $i,
                        'rand' => (string) $response->getBody(),
                        'time' => (microtime(true) - $time) * 1000,
                        'memory' => memory_get_usage(),
                    ]));
                });
                return null;
            };

            if ($options->async) {
                // async call with concurrent fibers
                EventLoop::delay(0, $closure);
            } else {
                //$suspension = EventLoop::getSuspension();
                //EventLoop::delay(0, static fn() => $suspension->resume());
                //$suspension->suspend();

                // synchronous series call
                $closure();
            }
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    function onOpen(ConnectionInterface $conn) {
        // TODO: Implement onOpen() method.
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
}
