<?php

namespace App\Services\Authenticator;

interface Authenticator
{
    public function setup();

    public function generate();

    public function verify();
}