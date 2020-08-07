<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'state', 'scan_start', 'heartbeat'
    ];
    //
    /**
     * Get the result record associated with the scan.
     */
    public function result()
    {
        return $this->hasOne('App\Result');
    }

    /**
     * Get the failuremessage record associated with the scan.
     */
    public function failureMessage()
    {
        return $this->hasOne('App\FailureMessage');
    }

    /**
     * Get the endpoint that owns the scan.
     */
    public function endpoint()
    {
        return $this->belongsTo('App\Endpoint');
    }
}
