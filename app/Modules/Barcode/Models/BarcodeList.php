<?php

namespace App\Modules\Barcode\Models;

use Illuminate\Database\Eloquent\Model;

class BarcodeList extends Model
{

    public function customer(){
        return $this->belongsTo('App\Modules\Customer\Models\CustomerModel');
    }

}
