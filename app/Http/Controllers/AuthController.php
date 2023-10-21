<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    //
    /**
    * @OA\Post(
    *    path="/api/register",
    *    tags={"Authentication"},
    *    summary="Register Api",
        *    operationId="User Register",
        *    @OA\Parameter(
        *        name="name",
        *        in="query",
        *        required=true,
        *        @OA\Schema(
        *            type="string",
        *            example="UserSample1"
        *        )
        *    ),
        *    @OA\Parameter(
        *        name="email",
        *        in="query",
        *        required=true,
        *        @OA\Schema(
        *            type="string",
        *            example="UserSample1@gmail.com"
        *        )
        *    ),
        *    @OA\Parameter(
        *        name="password",
        *        in="query",
        *        required=true,
        *        @OA\Schema(
        *            type="string",
        *            example="UserSample1"
        *        )
        *    ),
        *    @OA\Response(
        *        response=200,
        *        description="Success",
        *        @OA\MediaType(
        *            mediaType="application/json",
        *        )
        *    ),
        *    @OA\Response(
        *        response=401,
        *        description="Unauthorized"
        *    ),
        *    @OA\Response(
        *        response=400,
        *        description="Invalid request"
        *    ),
        *    @OA\Response(
        *        response=403,
        *        description="Unauthorized Access"
        *    ),
        *    @OA\Response(
        *        response=404,
        *        description="not found"
        *    ),
        *)
    */
    public function register() {
        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = new User;
        $user->name = request()->name;
        $user->email = request()->email;
        $user->password = bcrypt(request()->password);
        $user->save();

        return response()->json($user, 201);
    }

    /**
    * @OA\Post(
    *    path="/api/login",
    *    tags={"Authentication"},
    *    summary="Login Api",
        *    operationId="User Login",
        *    @OA\Parameter(
        *        name="email",
        *        in="query",
        *        required=true,
        *        @OA\Schema(
        *            type="string",
        *            example="UserSample1@gmail.com"
        *        )
        *    ),
        *    @OA\Parameter(
        *        name="password",
        *        in="query",
        *        required=true,
        *        @OA\Schema(
        *            type="string",
        *            example="UserSample1"
        *        )
        *    ),
        *    @OA\Response(
        *        response=200,
        *        description="Success",
        *        @OA\MediaType(
        *            mediaType="application/json",
        *        )
        *    ),
        *    @OA\Response(
        *        response=401,
        *        description="Unauthorized"
        *    ),
        *    @OA\Response(
        *        response=400,
        *        description="Invalid request"
        *    ),
        *    @OA\Response(
        *        response=403,
        *        description="Unauthorized Access"
        *    ),
        *    @OA\Response(
        *        response=404,
        *        description="not found"
        *    ),
        *)
    */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
    * @OA\Post(
    *    path="/api/auth/me",
    *    tags={"Authentication"},
    *    summary="Me Api",
        *    operationId="Get Login User Details",
        *    @OA\Response(
        *        response=200,
        *        description="Success",
        *        @OA\MediaType(
        *            mediaType="application/json",
        *        )
        *    ),
        *    @OA\Response(
        *        response=401,
        *        description="Unauthorized"
        *    ),
        *    @OA\Response(
        *        response=400,
        *        description="Invalid request"
        *    ),
        *    @OA\Response(
        *        response=403,
        *        description="Unauthorized Access"
        *    ),
        *    @OA\Response(
        *        response=404,
        *        description="not found"
        *    ),
        *   security={{ "apiAuth": {} }}
        *)
    */
    public function me()
    {
        return response()->json(auth()->guard('api')->user());
    }

    /**
    * @OA\Post(
    *    path="/api/auth/logout",
    *    tags={"Authentication"},
    *    summary="logout Api",
        *    operationId="",
        *    @OA\Response(
        *        response=200,
        *        description="Success",
        *        @OA\MediaType(
        *            mediaType="application/json",
        *        )
        *    ),
        *    @OA\Response(
        *        response=401,
        *        description="Unauthorized"
        *    ),
        *    @OA\Response(
        *        response=400,
        *        description="Invalid request"
        *    ),
        *    @OA\Response(
        *        response=403,
        *        description="Unauthorized Access"
        *    ),
        *    @OA\Response(
        *        response=404,
        *        description="not found"
        *    ),
        *   security={{ "apiAuth": {} }}
        *)
    */
    public function logout()
    {
        auth()->guard('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
    * @OA\Post(
    *    path="/api/auth/refresh",
    *    tags={"Authentication"},
    *    summary="refresh Api",
        *    operationId="Generate New Token for Login User",
        *    @OA\Response(
        *        response=200,
        *        description="Success",
        *        @OA\MediaType(
        *            mediaType="application/json",
        *        )
        *    ),
        *    @OA\Response(
        *        response=401,
        *        description="Unauthorized"
        *    ),
        *    @OA\Response(
        *        response=400,
        *        description="Invalid request"
        *    ),
        *    @OA\Response(
        *        response=403,
        *        description="Unauthorized Access"
        *    ),
        *    @OA\Response(
        *        response=404,
        *        description="not found"
        *    ),
        *   security={{ "apiAuth": {} }}
        *)
    */
    public function refresh()
    {
        return $this->respondWithToken(auth()->guard('api')->refresh());
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('api')->factory()->getTTL() * 60
        ]);
    }

}

