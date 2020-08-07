<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'alerts', 'warnings', 'notices', 'details'
    ];

    /**
     * Get the scan associated with this result.
     */
    public function scan()
    {
        return $this->belongsTo('App\Scan');
    }
}
