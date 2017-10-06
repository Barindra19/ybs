<?php

namespace App\Modules\Archive\Models;

use Illuminate\Database\Eloquent\Model;

class Archive extends Model
{

    public function cusromer(){
        return $this->belongsTo('App\Modules\Customer\Models\CustomerModel');
    }


}
