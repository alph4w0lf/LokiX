<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\User;

class UserMgmtController extends Controller
{
    // Handle list users request
    public function list_users(){
        $users = User::all();
        $output = ['users' => []];
        foreach($users as $uuser){
            $output['users'][] = [
                'controls' => $uuser->id,
                'fullname' => $uuser->name,
                'email' => $uuser->email,
                'last_login' => $uuser->last_login
            ];
        }
        return $output;
    }

    // Handle delete user request
    public function delete_user(Request $request){
        // validate received input
        $validator = Validator::make($request->all(), [
            'user_id' => 'numeric',
        ]);

        if($validator->fails()){
            return [
                'state' => 'failed',
                'reason' => 'invalid parameters'
            ];
        } else {
            $user = User::find($request->user_id);
            if($user){
                if($user->id != Auth::id()){
                    $user->delete();
                } else {
                    return [
                        'state' => 'failed',
                        'reason' => 'you can\'t delete your own user'
                    ];
                }
                
            } else {
                return [
                    'state' => 'failed',
                    'reason' => 'user doesn\'t exist'
                ];
            }
                
            return [
                'state' => 'success',
                'reason' => ''
            ];
        }
    }

    // Handle add user request
    public function add_user(Request $request){
        // validate received input
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|max:191',
            'email' => 'required|max:191|email',
            'password' => 'required|max:50'
        ]);

        if($validator->fails()){
            return [
                'state' => 'failed',
                'reason' => 'invalid parameters'
            ];
        } else {
            // Handle request
            $user = User::firstWhere('email', $request->email);
            if(!$user){
                $new_user = new User([
                    'name' => $request->fullname,
                    'email' => $request->email,
                    'password' => Hash::make($request->password)
                ]);
                $new_user->save();
                return [
                    'state' => 'success',
                    'reason' => ''
                ];
            } else {
                return [
                    'state' => 'failed',
                    'reason' => 'user already exists'
                ];
            }
        }
    }
}
