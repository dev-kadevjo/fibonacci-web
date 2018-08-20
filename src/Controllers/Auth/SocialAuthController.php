<?php

namespace Kadevjo\Fibonacci\Controllers\Auth;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Kadevjo\Fibonacci\Fibonacci;
//use Kadevjo\Fibonacci\Models\Client as User;
use Validator;

class SocialAuthController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function login(Request $request)
    {
      $userClass = config('fibonacci')['auth-social']['model'];

      $validator = Validator::make($request->json()->all(), [
        'provider' => 'required',
        'identifier'  => 'required',
        'verifier' => 'required'
      ]);
      if( $validator->fails() ) {
        return response()->json( array('error'=>'not enough data'));
      }

      $provider = $request->json('provider');
      $socialID = $request->json('identifier');
      $token = $request->json('verifier');
      $account =  Fibonacci::authenticateSocial($provider, $socialID, $token);
      
      //$user = User::whereNotNull('email')->where('email', $account->email)->first();
      $user = $userClass::whereNotNull('email')->where('email', $account->email)->first();
      if( $user == null ) {
        $user = new $userClass();
        $user->first_name = $account->first_name;
        $user->last_name = $account->last_name;
        $user->email = $account->email;
        $user->avatar = $account->picture;
        $user->save();
      }

      $token = \JWTAuth::fromUser($user);
      return response()->json(['user'=>$user,'token'=>$token]);
    }    
}
