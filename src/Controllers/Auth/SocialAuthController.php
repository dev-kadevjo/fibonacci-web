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
use Kadevjo\Fibonacci\Models\Client as ClientAuthenticable;
use Validator;

class SocialAuthController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function login(Request $request)
    {
      $userClass = config('fibonacci.auth.model');

      if(is_null($userClass))        
          return response()->json( array('error'=>"Model isn't defined"));
          
      if(!$user = \App::make($userClass)  instanceof ClientAuthenticable)
          return response()->json( array('error'=>"Model isn't extends of fibonacci client"));

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
      $user = $userClass::whereNotNull('email')->where('email', $account->email)->first();
      if( $user == null ) {
        $user = new $userClass();
        $user->name = "{$account->first_name} {$account->last_name}";
        $user->email = $account->email;
        $user->picture_url = $account->picture;
        $user->save();
      }else{
        $user->name = "{$account->first_name} {$account->last_name}";
        $user->email = $account->email;
        $user->picture_url = $account->picture;
        $user->save();
      }


      $token = \JWTAuth::fromUser($user);
      return response()->json(['user'=>$user,'token'=>$token]);
    }    
}
