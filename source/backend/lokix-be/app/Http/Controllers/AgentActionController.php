<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Loki;

class AgentActionController extends Controller
{
    // Handle fetch agent status request
    public function agent_status(){
        $loki = Loki::find(1);
        return [
            'is_ready' => $loki->ready,
            'is_updating' => $loki->updating,
            'last_update' => $loki->last_update,
            'last_error' => $loki->error
        ];
    }

    // Handle start update request
    public function start_update(){
        // Execute python script to do its magic or do it with php
        $loki = Loki::find(1);
        if($loki->updating)
            return [
                'state' => 'failed',
                'reason' => 'an update is already in progress'
            ];
        $loki->ready = false;
        $loki->updating = true;
        $loki->error = '';
        $loki->last_update = now();
        $loki->token = Hash::make('loki'.now());
        $loki->save();
        // Start the LokiX Update python script
        exec("/usr/bin/python ".Storage::path('loki_update.py')." '".$loki->token."' > /dev/null &");

        return [
            'state' => 'success',
            'reason' => ""
        ];
    }

    // Handle python loki updater request to update its status
    public function done_update(Request $request){
        // validate received input
        $validator = Validator::make($request->all(), [
            'token' => 'required|max:191',
            'error' => 'required|max:191'
        ]);

        if($validator->fails()){
            return [
                'state' => 'failed',
                'reason' => 'invalid parameters'
            ];
        } else {
            // Check that the supplyed token matches the one in the database
            $loki = Loki::where('token', $request->token)->first();
            if(!$loki){
                return [
                    'state' => 'failed',
                    'reason' => 'invalid token'
                ];
            }
            // token is correct, update loki update status
            $loki->last_update = now();
            $loki->updating = false;
            $loki->error = $request->error;
            if($request->error == 'success'){
                $loki->ready = true;
            } else {
                $loki->ready = false;
            }
            $loki->save();
        }

        return [
            'state' => 'success',
            'reason' => ''
        ];
    }

    // Handle download agent request
    public function download_agent(Request $request){
        $loki = Loki::find(1);
        if($loki->ready){
            // Embed the platform IP in the agent if not previously done
            if(!$loki->appended){
                $platform_ip = $request->server('SERVER_ADDR');
                $file_ptr = fopen('../storage/app/agent.exe', 'a');
                fwrite($file_ptr, '#'.gethostbyname(gethostname()).'#');
                fclose($file_ptr);
                $loki->appended = true;
                $loki->save();
            }
            
            return Storage::download('agent.exe');
        } else {
            return [
                'state' => 'failed',
                'reason' => 'you have to update the agent first'
            ];
        }
    }
}
