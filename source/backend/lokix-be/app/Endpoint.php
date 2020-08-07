<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Endpoint extends Model
{
    /**
     * Get the scan record associated with the endpoint.
     */
    public function scan()
    {
        return $this->hasOne('App\Scan');
    }
}
