<?php

namespace Beam\BeamCore\Concerns;

use Beam\BeamCore\Actions\Config;
use Beam\BeamCore\Beam;

trait Colors
{
    public function danger(): Beam
    {
        if (Config::get('config.color_in_screen')) {
            return $this->toScreen('danger');
        }

        return $this->color('red');
    }

    public function dark(): Beam
    {
        return $this->color('black');
    }

    public function warning(): Beam
    {
        if (boolval(Config::get('config.color_in_screen'))) {
            return $this->toScreen('warning');
        }

        return $this->color('orange');
    }

    public function success(): Beam
    {
        if (boolval(Config::get('config.color_in_screen'))) {
            return $this->toScreen('success');
        }

        return $this->color('green');
    }

    public function info(): Beam
    {
        if (boolval(Config::get('config.color_in_screen'))) {
            return $this->toScreen('info');
        }

        return $this->color('blue');
    }

    public function red(): Beam
    {
        return $this->danger();
    }

    public function blue(): Beam
    {
        return $this->info();
    }

    public function green(): Beam
    {
        return $this->success();
    }

    public function orange(): Beam
    {
        return $this->warning();
    }

    public function black(): Beam
    {
        return $this->dark();
    }
}
