<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Result;
use App\Scan;
use App\Endpoint;
use App\FailureMessage;

class ScansController extends Controller
{
    // List paginated completed scans
    public function Completed_Scans(Request $request){
        $completed_scans = DB::table('results')
        ->join('scans', 'scan_id', 'scans.id')
        ->join('endpoints', 'endpoint_id', 'endpoints.id')
        ->select('endpoints.hostname', 'endpoints.ip_address', 'results.alerts', 'results.warnings', 'results.notices', 'results.id')
        ->orderBy('alerts', 'desc')
        ->paginate(15);
        return $completed_scans;
    }

    // Search paginated completed scans
    public function Search_Completed_Scans(Request $request){
        // validate received input
        $validator = Validator::make($request->all(), [
            'search' => 'required|max:191',
        ]);

        if($validator->fails()){
            return [
                'state' => 'failed',
                'reason' => 'invalid parameters'
            ];
        } else {
            $completed_scans = DB::table('results')
            ->join('scans', 'scan_id', 'scans.id')
            ->join('endpoints', 'endpoint_id', 'endpoints.id')
            ->select('endpoints.hostname', 'endpoints.ip_address', 'results.alerts', 'results.warnings', 'results.notices', 'results.id')
            ->where('endpoints.hostname','like','%'.$request->search.'%')
            ->orWhere('endpoints.ip_address','like','%'.$request->search.'%')
            ->orderBy('alerts', 'desc')
            ->paginate(15);
            return $completed_scans;
        }

    }

    // List paginated progress scans
    public function Progress_Scans(Request $request){
        $running_scans = DB::table('scans')
        ->join('endpoints', 'endpoint_id', 'endpoints.id')
        ->select('endpoints.hostname', 'endpoints.ip_address', 'scans.scan_start')
        ->where('scans.state', 'running')
        ->paginate(15);
        return $running_scans;
    }

    // Search paginated progress scans
    public function Search_Progress_Scans(Request $request){
        // validate received input
        $validator = Validator::make($request->all(), [
            'search' => 'required|max:191',
        ]);

        if($validator->fails()){
            return [
                'state' => 'failed',
                'reason' => 'invalid parameters'
            ];
        } else {
            $running_scans = DB::table('scans')
            ->join('endpoints', 'endpoint_id', 'endpoints.id')
            ->select('endpoints.hostname', 'endpoints.ip_address', 'scans.scan_start')
            ->where('scans.state', 'running')
            ->where(function ($query) use($request) {
                $query->where('endpoints.hostname', 'like', '%'.$request->search.'%')
                ->orWhere('endpoints.ip_address', 'like', '%'.$request->search.'%');
            })
            ->paginate(15);
            return $running_scans;
        }
    }

    // List paginated failed scans
    public function Failed_Scans(Request $request){
        $failed_scans = DB::table('scans')
        ->join('endpoints', 'endpoint_id', 'endpoints.id')
        ->join('failure_messages', 'scans.id', 'failure_messages.scan_id')
        ->select('endpoints.hostname', 'endpoints.ip_address', 'scans.scan_start', 'scans.scan_end', 'failure_messages.reason')
        ->where('scans.state', 'failed')
        ->paginate(15);
        return $failed_scans;
    }

    // Search paginated failed scans
    public function Search_Failed_Scans(Request $request){
        // validate received input
        $validator = Validator::make($request->all(), [
            'search' => 'required|max:191',
        ]);

        if($validator->fails()){
            return [
                'state' => 'failed',
                'reason' => 'invalid parameters'
            ];
        } else {
            $failed_scans = DB::table('scans')
            ->join('endpoints', 'endpoint_id', 'endpoints.id')
            ->join('failure_messages', 'scans.id', 'failure_messages.scan_id')
            ->select('endpoints.hostname', 'endpoints.ip_address', 'scans.scan_start', 'scans.scan_end', 'failure_messages.reason')
            ->where('scans.state', 'failed')
            ->where(function ($query) use($request) {
                $query->where('endpoints.hostname', 'like', '%'.$request->search.'%')
                ->orWhere('endpoints.ip_address', 'like', '%'.$request->search.'%');
            })
            ->paginate(15);
            return $failed_scans;
        }
    }

    // Make running scans with a heartbeat older than an hour into failed scans
    public function Check_Heartbeat(){
        foreach (Scan::where('state', '=', 'running')
        ->where('heartbeat', '<', now()->subMinutes(10))
        ->cursor() 
        as $ill_scan){
            $ill_scan->failureMessage()->save(new FailureMessage([
                'reason' => 'Agent non-responsive for more than 10 minutes'
            ]));
            $ill_scan->scan_end = $ill_scan->heartbeat;
            $ill_scan->state = 'failed';
            $ill_scan->push();
        }

        return [
            'state' => 'success',
            'reason' => ''
        ];
    }
}
