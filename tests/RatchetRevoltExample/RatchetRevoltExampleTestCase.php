<?php
namespace RatchetRevoltExample;

use Amp\ReactAdapter\ReactAdapter;
use PHPUnit\Framework\TestCase;
use React\EventLoop\LoopInterface;

class RatchetRevoltExampleTestCase extends TestCase {
    protected LoopInterface $loop;

    public function setUp(): void {
        $this->loop = ReactAdapter::get();
    }
}
