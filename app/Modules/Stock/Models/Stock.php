<?php

namespace App\Modules\Stock\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    public function customer(){
        return $this->belongsTo('App\Modules\Customer\Models\CustomerModel');
    }
}
