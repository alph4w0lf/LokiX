<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;

class UserProfileController extends Controller
{
    // Handle fetch user profile request
    public function fetch_profile(){
        $user_id = Auth::id(); // Take it from auth later
        $user = User::find($user_id);
        return [
            'full_name' => $user->name,
            'email' => $user->email
        ];
    }

    // Handle update profile request
    public function update_profile(Request $request){
        $user_id = Auth::id(); // Take it later from auth

        // validate received input
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|max:191',
            'email' => 'required|max:191|email',
            'current_password' => 'required|max:50',
            'new_password' => 'required|max:50'
        ]);

        if($validator->fails()){
            return [
                'state' => 'failed',
                'reason' => 'invalid parameters'
            ];
        } else {
            $user = User::find($user_id);
            if(!Hash::check($request->current_password, $user->password))
                return [
                    'state' => 'failed',
                    'reason' => 'current password is not correct'
                ];

            $user->name = $request->full_name;
            $user->email = $request->email;
            $user->password = Hash::make($request->new_password);
            $user->save();
            return [
                'state' => 'success',
                'reason' => ''
            ];
        }
        

    }
}
