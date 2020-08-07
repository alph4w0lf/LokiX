<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Result;
use App\Endpoint;
use App\Scan;

class ResultsController extends Controller
{
    // Handle a request to display the results for an endpoint
    public function fetch_results(Request $request){
        // validate received input
        $validator = Validator::make($request->all(), [
            'control_id' => 'numeric'
        ]);

        if($validator->fails()){
            return [
                'state' => 'failed',
                'reason' => 'invalid parameters'
            ];
        } else {
            $result = Result::find($request->control_id);
            if($result){
                // Send result
                return [
                    'endpoint' => $result->scan->endpoint->hostname,
                    'ip_address' => $result->scan->endpoint->ip_address,
                    'alerts' => $result->alerts,
                    'warnings' => $result->warnings,
                    'notices' => $result->notices,
                    'results' => $result->details
                ];
            } else {
                return [
                    'state' => 'failed',
                    'reason' => 'no results exist for the provided ID'
                ];
            }
        }

    }
}
