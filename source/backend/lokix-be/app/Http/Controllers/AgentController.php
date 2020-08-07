<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Endpoint;
use App\Scan;
use App\FailureMessage;
use App\Result;

class AgentController extends Controller
{
    // Handle a start scan request from the agent
    public function ScanStart(Request $request){
        $start_status = 'failed';
        $fail_reason = '';
        $endpoint_token = '';

        // validate received input
        $validator = Validator::make($request->all(), [
            'hostname' => 'required|max:191',
            'ip_address' => 'required|max:15|ipv4'
        ]);
        
        if($validator->fails()){
            $fail_reason = 'invalid parameters';
        } else {
            // Protection against IP Address Spoofing
            if($request->ip_address != $request->ip()){
                return [
                    'status' => 'failed',
                    'reason' => 'ip address spoofing detected'
                ];
            }
            // Handle request
            $start_status = "success";

            // Create an Endpoint Token
            $endpoint_token = Hash::make($request->hostname.$request->ip_address);

            $existing_endpoint = Endpoint::where('hostname', $request->hostname)->where('ip_address', $request->ip_address)->first();
            if($existing_endpoint){
                // Check if it has a running scan first
                if($existing_endpoint->scan->state == 'running'){
                    return [
                        'status' => 'failed',
                        'reason' => 'a scan is already running for this endpoint'
                    ];
                }
                // Update an existing Endpoint/Delete Old Scans with all related models
                // Start a new scan
                // Update Endpoint Token
                $existing_endpoint->scan()->delete(); //cascade to all related models
                $existing_endpoint->scan()->save(new Scan([
                    'state' => 'running',
                    'scan_start' => now(),
                    'heartbeat' => now()
                    ]));
                $existing_endpoint->token = $endpoint_token;
                $existing_endpoint->save();
            } else {
                // Create a new Endpoint with Token and Scan
                $endpoint = new Endpoint();
                $endpoint->hostname = $request->hostname;
                $endpoint->ip_address = $request->ip_address;
                $endpoint->token = $endpoint_token;
                $endpoint->save();
                $endpoint->scan()->save(new Scan([
                    'state' => 'running',
                    'scan_start' => now(),
                    'heartbeat' => now()
                    ]));
            }
        }
        
        if($start_status == 'failed'){
            return [
                'status' => $start_status,
                'reason' => $fail_reason
            ];
        } else {
            return [
                'status' => $start_status,
                'token' => $endpoint_token
            ];
        }
        
    }

    // Handle a scan failed request from the agent
    public function ScanFail(Request $request){
        $api_status = 'failed';
        $fail_reason = '';

        // validate received input
        $validator = Validator::make($request->all(), [
            'token' => 'required|max:191',
            'reason' => 'required|max:255'
        ]);
        
        if($validator->fails()){
            $fail_reason = 'invalid parameters';
        } else {
            // Handle Request
            $existing_endpoint = Endpoint::where('token', $request->token)->first();
            if($existing_endpoint){
                // Update Scan Status & Insert a failure message
                $api_status = 'success';
                $existing_endpoint->scan->state = 'failed';
                $existing_endpoint->scan->scan_end = now();
                $existing_endpoint->scan->heartbeat = now();
                $existing_endpoint->push();
                if($existing_endpoint->scan->has('failureMessage')->count() == 0){
                    $existing_endpoint->scan->failureMessage()->save(new FailureMessage([
                        'reason'=>$request->reason
                        ]));
                } else {
                    $api_status = 'failed';
                    $fail_reason = 'failure message already recorded';
                }
                
            } else {
                $fail_reason = 'no scan exists for this endpoint';
            }
        }

        return [
            'status' => $api_status,
            'reason' => $fail_reason
        ];
    }

    // Handle a heartbeat request from the agent
    public function HeartBeat(Request $request){
        $api_status = 'failed';
        $fail_reason = '';

        // validate received input
        $validator = Validator::make($request->all(), [
            'token' => 'required|max:191',
        ]);
        
        if($validator->fails()){
            $fail_reason = 'invalid parameters';
        } else {
            // Handle request
            $existing_endpoint = Endpoint::where('token', $request->token)->first();
            if($existing_endpoint){
                if($existing_endpoint->has('scan')->count() > 0){
                    // Update scan heartbeat
                    $api_status = 'success';
                    $existing_endpoint->scan->heartbeat = now();
                    $existing_endpoint->push();
                } else {
                    $fail_reason = 'no scan exists for this endpoint';
                }

            } else {
                $fail_reason = 'no scan exists for this endpoint';
            }
        }

        return [
            'status' => $api_status,
            'reason' => $fail_reason
        ];
    }

    // Handle a completed scan request
    public function ScanDone(Request $request){
        $api_status = 'failed';
        $fail_reason = '';

        // validate received input
        $validator = Validator::make($request->all(), [
            'token' => 'required|max:191',
            'alerts' => 'required|numeric',
            'warnings' => 'required|numeric',
            'notices' => 'required|numeric',
            'results' => 'required'
        ]);

        if($validator->fails()){
            $fail_reason = 'invalid parameters';
        } else {
            // Handle request
            $existing_endpoint = Endpoint::where('token', $request->token)->first();
            if($existing_endpoint){
                if($existing_endpoint->has('scan') && $existing_endpoint->scan->state == 'running'){
                    $api_status = 'success';
                    $existing_endpoint->scan->state = 'completed';
                    $existing_endpoint->scan->heartbeat = now();
                    $existing_endpoint->scan->scan_end = now();
                    $existing_endpoint->push();
                    $existing_endpoint->scan->result()->save(new Result([
                        'alerts' => $request->alerts,
                        'warnings' => $request->warnings,
                        'notices' => $request->notices,
                        'details' => $request->results
                        ]));
                } else {
                    $fail_reason = 'no scan exists for this endpoint';
                }
            } else {
                $fail_reason = 'no scan exists for this endpoint';
            }
        }

        return [
            'status' => $api_status,
            'reason' => $fail_reason
        ];
    }

    // Handle the Get Loki request - Download Loki Zip File
    public function GetLoki(){
        return Storage::download('loki.tar.gz');
    }
}
