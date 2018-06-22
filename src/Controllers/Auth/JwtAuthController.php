<?php

namespace Kadevjo\Fibonacci\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Kadevjo\Fibonacci\Models\Client;
use Illuminate\Support\Facades\Hash;



class JwtAuthController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    protected $guard = 'api';

    public function __construct()
    {
        //$this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        if ($token = auth('api')->attempt($credentials)) {
            $user = \Kadevjo\Fibonacci\Models\Client::where('email',$credentials['email'])->first();
            return response()->json(['user'=>$user,'token'=>$token]);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function signup(Request $request) {
        $requestData = $request->all();

        $check = Client::where('email', '=', $requestData['email'])->first();
        if ($check){
          return response()->json(['error' => 'email already exist'], 401);
        }

        $newUser = new Client;
        $newUser->email = $requestData['email'];
        $newUser->password =  Hash::make($requestData['password']);

        if(isset($requestData['avatar']))
        $newUser->avatar = $requestData['avatar'];

        if(isset($requestData['first_name']))
        $newUser->first_name = $requestData['first_name'];

        if(isset($requestData['last_name']))
        $newUser->last_name = $requestData['last_name'];
        $credentials = request(['email', 'password']);

        $newUser->save();
        if ($token = \JWTAuth::fromUser($newUser, $credentials)) {
            return response()->json(['user'=>$newUser,'token'=>$token]);
        }
        return response()->json(['error' => 'Invalid parameters'], 401);

      }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(\JWTAuth::parseToken()->authenticate());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $user = \JWTAuth::parseToken()->authenticate();
        return response()->json(['user'=>$user,'token'=>auth('api')->refresh()]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
            ]);
    }
}
