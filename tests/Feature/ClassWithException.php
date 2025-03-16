<?php

namespace Beam\BeamCore\Tests\Feature;

use Beam\BeamCore\Support\CodeSnippet;

class ClassWithException
{
    public function __construct()
    {
        $this->handleCodeSnippet();
    }

    public function handleCodeSnippet(int $linesAbove = 6, int $linesBelow = 6)
    {
        $exception = new \Exception('Error!');

        return (new CodeSnippet($linesAbove, $linesBelow))->fromException($exception);
    }
}
