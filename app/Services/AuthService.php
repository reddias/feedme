<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService implements AuthServiceInterface
{
    /**
     * Attempt to log in a user with additional checks.
     *
     * @param array $credentials
     * @return string|null
     */
    public function attemptLogin(array $credentials): ?string
    {
        // Check for user existence and status
        $user = User::query()->where('email', $credentials['email'])
            ->active()
            ->first();

        // Attempt authentication
        if ($user && Auth::attempt($credentials)) {
            return Auth::login($user);
        }

        return null;
    }

}
