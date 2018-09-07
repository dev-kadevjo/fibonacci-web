<?php

namespace Kadevjo\Fibonacci\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Kadevjo\Fibonacci\Models\Client as ClientAuthenticable;
use Validator;



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
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $userClass = config('fibonacci.auth.model');
        
        if(is_null($userClass))        
            return response()->json( array('error'=>"Model {$userClass} isn't defined"));
        
        if(!$user = \App::make($userClass)  instanceof ClientAuthenticable)
            return response()->json( array('error'=>"Model {$userClass} should extends of fibonacci client"));

        $credentials = request(['email', 'password']);
        
        if ($token = auth('api')->attempt($credentials)) 
        {
            $user = $userClass::where('email',$credentials['email'])->first();
            return response()->json(['user'=>$user,'token'=>$token]);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function signup(Request $request) 
    {
        $userClass = config('fibonacci.auth.model');

        if(is_null($userClass))        
            return response()->json( array('error'=>"Model {$userClass} isn't defined"));
        
        if(!$user = \App::make($userClass)  instanceof ClientAuthenticable)
            return response()->json( array('error'=>"Model {$userClass} should extends of fibonacci client"));

        $validation =Validator::make($request->all(),[
            //client validation
            'email'=> 'required|email',
            'name' => 'alpha_dash',
            'password' =>'required',
        ]);

        if ($validation->fails()) 
        return response()->json(['error' => $validation->errors()], 400);   

        $requestData = $request->all();

        $check = $userClass::where('email', '=', $requestData['email'])->first();
        if ($check){
          return response()->json(['error' => 'email already exist'], 401);
        }

        $user = new $userClass;

        $user->email = $requestData['email'];
        
        $user->password =  $requestData['password'];

        if(isset($requestData['picture']))
            $user->picture = $requestData['picture'];

        if(isset($requestData['name']))
            $user->name = $requestData['name'];

        $credentials = request(['email', 'password']);

        $user->save();
        if ($token = auth('api')->attempt($credentials)) {
            return response()->json(['user'=>$user,'token'=>$token]);
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
