<?php
namespace Middlewares;

use Src\Request;
use Src\Auth\Auth;
class ApiAuthMiddleware
{
    public function handle(Request $request, string $requiredRole = null)
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !preg_match('/^Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = $matches[1];

        $userData = Auth::validateToken($token);

        if (!$userData) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $request->user = $userData;

        if (!is_null($requiredRole)) {
            $requiredRoleId = (int)$requiredRole;

            if ($userData->role_id !== $requiredRoleId) {
                return response()->json(['error' => 'Access denied'], 403);
            }
        }
        return;
    }
}