<?php

namespace Kadevjo\Fibonacci\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Validator;

class AuthAPIController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function socialAuth()
    {
        $validator = Validator::make($request->json()->all(), [
            'socialID'  => 'required',
            'provider' => 'required',
            'access_token' => 'required'
        ]);

        if( $validator->fails() ) {
            return ResponseHelper::GetResponse('not_enough_data');
        }

        $socialID = $request->json('socialID');
        $provider = $request->json('provider');
        $access_token = $request->json('access_token');

        $account = Fibonacci::authenticateSocial($socialID, $provider, $access_token);

        $user = User::withTrashed()->whereNotNull('email')->where('email', $account->email)->first();

        if( $user == null ) {
            $user = new User();
            $user->first_name = $account->first_name;
            $user->last_name = $account->last_name;
            $user->email = $account->email;
            $user->picture = $account->picture;
            $user->save();
        }
        
        $user = $user;

        $token = $user->createToken('Laravel Personal Access Client');

        return response()->json($token);

    }
}