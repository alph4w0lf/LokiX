<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FailureMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reason'
    ];
    //
    /**
     * Get the scan that owns the result.
     */
    public function scan()
    {
        return $this->belongsTo('App\Scan');
    }
}
