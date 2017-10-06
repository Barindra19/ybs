<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemDetail extends Model
{
    public function stock(){
        return $this->belongsTo('App\Modules\Stock\Models\Stock');
    }

    public function order_item(){
        return $this->belongsTo('App\Modules\Order\Models\OrderItemModel');
    }


}
