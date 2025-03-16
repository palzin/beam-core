<?php

namespace Beam\BeamCore\Payloads;

class Label
{
    public function __construct(
        public string $label = 'dump',
    ) {
    }
}
