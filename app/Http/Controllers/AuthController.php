<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{

    private AuthService $authService;


    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authService = new AuthService();
    }


    /**
     * Get the token array structure.
     *
     * @param string $token
     * @param int|null $expirationTime
     * @return JsonResponse
     */
    protected function respondWithToken(string $token, int $expirationTime = null): JsonResponse
    {
        $expirationTime = $expirationTime ?? config('jwt.ttl') * 60;

        return $this->me()
            ->header('Authorization', 'Bearer ' . $token)
            ->withHeaders(['expires_in' => $expirationTime]);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return JsonResponse
     */
    public function login(): JsonResponse
    {
        $credentials = request(['email', 'password']);
        $token = $this->authService->attemptLogin($credentials);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);

    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $response = [
            'user_id' => auth()->id(),
            'name' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
            'email' => auth()->user()->email,
        ];
        return response()->json($response);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        return $this->respondWithToken(auth()->refresh());
    }

}
