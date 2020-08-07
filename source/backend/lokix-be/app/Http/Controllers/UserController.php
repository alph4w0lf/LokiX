<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\User;

class UserController extends Controller
{
    // Handle logout request
    public function logout()
    {
        Auth::logout();
        return [
            'state' => 'success',
            'reason' => ''
        ];
    }

    protected function guard()
    {
        return Auth::guard();
    }

    // Handle login request
    public function login(Request $request){
        $credentials = $request->only('email', 'password');

        // validate received input
        $validator = Validator::make($credentials, [
            'email' => 'required|max:191',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return [
                'state' => 'failed',
                'reason' => 'invalid parameters'
            ];
        } else {
            if(Auth::attempt($credentials)){
                $this_user = User::find(Auth::id());
                $this_user->last_login = now();
                $this_user->save();
                return [
                    'state' => 'success',
                    'reason' => ''
                ];
            } else {
                return [
                    'state' => 'failed',
                    'reason' => 'email and password do not match our records'
                ];
            }
        }
    }
}
