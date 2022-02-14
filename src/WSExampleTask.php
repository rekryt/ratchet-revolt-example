<?php
namespace RatchetRevoltExample;

use Amp\ReactAdapter\ReactAdapter;
use React\Http\Browser;
use React\Promise\PromiseInterface;
use function Amp\delay;

/**
 * Class WSExampleTask
 * @package RatchetRevoltExample
 */
class WSExampleTask {
    /**
     * @return PromiseInterface
     */
    public function execute(): PromiseInterface {
        //delay(0); // delay gets memory leaks >((
        $client = new Browser(null, ReactAdapter::get());
        return $client->get('http://127.0.0.1/api');
    }
}
