<?php
namespace Example;

use function Amp\delay;

class WSExampleTask {
    public function execute() {
        delay(5);
        return rand(0, 100);
    }
}
