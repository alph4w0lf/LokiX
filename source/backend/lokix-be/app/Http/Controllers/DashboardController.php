<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Endpoint;
use App\Scan;
use App\Result;

class DashboardController extends Controller
{
    // Handle dashboard statistics request
    public function stats(){
        
        return [
            'total_endpoints' => Endpoint::all()->count(),
            'completed_scans' => Scan::where('state','completed')->count(),
            'running_scans' => Scan::where('state','running')->count(),
            'failed_scans' => Scan::where('state','failed')->count()
        ];
    }

    // Handle dashboard top 10 infected request
    public function top_infected(){
        $output = ['top_infected' => []];
        foreach (Result::where('alerts', '>=', 1)->orderBy('alerts', 'desc')->take(10)->cursor() as $result){
            $output['top_infected'][] = [
                'endpoint' => $result->scan->endpoint->hostname,
                'ipaddress' => $result->scan->endpoint->ip_address,
                'alerts' => $result->alerts,
                'warnings' => $result->warnings,
                'notices' => $result->notices,
                'controls' => $result->id
            ];
        }
        return $output;

    }

    // Handle dashboard top 10 suspected request
    public function top_suspected(){
        $output = ['top_suspected' => []];
        foreach (Result::where('warnings', '>=', 1)->orderBy('warnings', 'desc')->take(10)->cursor() as $result){
            $output['top_suspected'][] = [
                'endpoint' => $result->scan->endpoint->hostname,
                'ipaddress' => $result->scan->endpoint->ip_address,
                'alerts' => $result->alerts,
                'warnings' => $result->warnings,
                'notices' => $result->notices,
                'controls' => $result->id
            ];
        }
        return $output;

    }
}
