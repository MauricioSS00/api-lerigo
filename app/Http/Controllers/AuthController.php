<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only(["email", "password"]);
        if (!$token = auth("api")->attempt($credentials)) {
            return response()->json(["error" => "Unauthorized"], 401);
        }
        return $this->respondWithToken($token);
    }

    /**
     * @return JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth("api")->refresh());
    }

    /**
     * @param $token
     * @return JsonResponse
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            "access_token" => $token,
            "token_type" => "bearer"
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function logout()
    {
        auth("api")->logout();
        return response()->json(["message" => "Logout realizado com sucesso"]);
    }
}
