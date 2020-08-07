<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/////////////////// Frontend API Routes ///////////////////////
Route::middleware('auth:sanctum')->get('/state', function (Request $request) {
    return [
        'state' => 'success',
        'reason' => 'you are authenticated'
    ];
});

Route::get('/failed/auth', function (Request $request) {
    return [
        'state' => 'failed',
        'reason' => 'authentication is required'
    ];
})->name('failedAuth');

Route::post('/login', 'UserController@login');
Route::post('/logout', 'UserController@logout');

// Retrieve Dashboard Stats
Route::middleware('auth:sanctum')->get('/dashboard_stats', 'DashboardController@stats');
// Retrieve Top 10 Infected Stats
Route::middleware('auth:sanctum')->get('/top_infected', 'DashboardController@top_infected');
// Retrieve Top 10 Suspected Stats
Route::middleware('auth:sanctum')->get('/top_suspected', 'DashboardController@top_suspected');
// Retrieve Results for the Results page
Route::middleware('auth:sanctum')->get('/results/{control_id}', 'ResultsController@fetch_results');
// Retrieve Users List
Route::middleware('auth:sanctum')->get('/users_list', 'UserMgmtController@list_users');
// Delete User
Route::middleware('auth:sanctum')->post('/delete_user/{user_id}', 'UserMgmtController@delete_user');
// Add User
Route::middleware('auth:sanctum')->post('/add_user', 'UserMgmtController@add_user');
// Fetch user profile data
Route::middleware('auth:sanctum')->get('/user_profile', 'UserProfileController@fetch_profile');
// Fetch agent software status
Route::middleware('auth:sanctum')->get('/agent/status', 'AgentActionController@agent_status');
// Start Update Agent software
Route::middleware('auth:sanctum')->post('/agent/update', 'AgentActionController@start_update');
// Download the agent
Route::get('/agent/download', 'AgentActionController@download_agent');
// Update user profile
Route::middleware('auth:sanctum')->post('/update/profile', 'UserProfileController@update_profile');
// Retrieve Completed Scans
Route::middleware('auth:sanctum')->get('/completed/scans', 'ScansController@Completed_Scans');
// Search Completed Scans
Route::middleware('auth:sanctum')->post('/search/completed/scans', 'ScansController@Search_Completed_Scans');
// Retrieve in Progress Scans
Route::middleware('auth:sanctum')->get('/progress/scans', 'ScansController@Progress_Scans');
// Search Progress Scans
Route::middleware('auth:sanctum')->post('/search/progress/scans', 'ScansController@Search_Progress_Scans');
// Retrieve Failed Scans
Route::middleware('auth:sanctum')->get('/failed/scans', 'ScansController@Failed_Scans');
// Search Failed Scans
Route::middleware('auth:sanctum')->post('/search/failed/scans', 'ScansController@Search_Failed_Scans');
// Remove scans with non-responsive agents
Route::get('/check/heartbeat', 'ScansController@Check_Heartbeat');

////////////// Python Script API Routes /////////////////
// Tell LokiX platform about the update status
Route::post('/agent/update/status', 'AgentActionController@done_update');

///////////////// Agent API Routes /////////////////
// Tell the platform you are starting a scan
Route::post('/scan_start', 'AgentController@ScanStart');
// Tell the platform the scan is failed
Route::post('/scan_fail', 'AgentController@ScanFail');
// Tell the platform you are still running
Route::post('/heartbeat', 'AgentController@HeartBeat');
// Tell the platform the scan is completed
Route::post('/scan_done', 'AgentController@ScanDone');
// Get Loki compressed File
Route::get('/get/loki', 'AgentController@GetLoki');
