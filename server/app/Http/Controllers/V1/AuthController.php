<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var \Tymon\JWTAuth\JWTAuth
     */
    protected $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {
            if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], Response::HTTP_BAD_REQUEST);
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], Response::HTTP_BAD_REQUEST);
        } catch (JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(compact('token'));
    }

    public function refresh() {

        try {
            $this->jwt->getToken();
            if (! $token = $this->jwt->refresh()) {
                return response()->json(['token_invalid'], Response::HTTP_NOT_FOUND);
            }
        } catch (TokenExpiredException $e) {
            return response()->json(['token_expired'], Response::HTTP_BAD_REQUEST);
        } catch (TokenInvalidException $e) {
            return response()->json(['token_invalid'], Response::HTTP_BAD_REQUEST);
        } catch (JWTException $e) {
            return response()->json(['token_absent' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(compact('token'));
    }
}