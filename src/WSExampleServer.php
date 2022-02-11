<?php
namespace Example;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use function Amp\async;
use function Amp\Future\await;

/**
 * Class WSExampleServer
 * @package Example
 */
class WSExampleServer implements MessageComponentInterface {
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

    /**
     * @param ConnectionInterface $conn
     * @param MessageInterface $msg
     */
    public function onMessage(ConnectionInterface $conn, MessageInterface $msg) {
        $task = new WSExampleTask();
        $options = json_decode($msg);

        if ($options->async) {
            $promises = [];
            for ($i = 0; $i < $options->count; $i++) {
                $promises[] = async(static function () use ($conn, $task): void {
                    $result = ['rand' => $task->execute(), 'time' => microtime(true)];
                    $conn->send(json_encode($result));
                });
            }
            await($promises);
        } else {
            for ($i = 0; $i < $options->count; $i++) {
                $result = ['rand' => $task->execute(), 'time' => microtime(true)];
                $conn->send(json_encode($result));
            }
        }
    }
}
