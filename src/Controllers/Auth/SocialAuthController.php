<?php

namespace Kadevjo\Fibonacci\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SocialAuthController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function login(Request $request)
    {
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
      $token = json_decode($request->json('verifier'),true);
      $account =  Fibonacci::authenticateSocial($provider, $socialID, $token);
      
      $user = User::whereNotNull('email')->where('email', $account->email)->first();
      if( $user == null ) {
        $user = new User();
        $user->first_name = $account->first_name;
        $user->last_name = $account->last_name;
        $user->email = $account->email;
        $user->avatar = $account->picture;
        $user->save();
      }
      $token = $user->createToken(config('fibonacci.auth-social.passport.token'));
      return response()->json(['user'=>$user,'token'=>$token->accessToken]);
    }
    
}
