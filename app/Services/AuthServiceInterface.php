<?php

namespace App\Services;

interface AuthServiceInterface
{
    function attemptLogin(array $credentials): ?string;
}
