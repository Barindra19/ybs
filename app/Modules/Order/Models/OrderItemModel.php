<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemModel extends Model
{

    protected $table = 'order_items';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }

    public function branch(){
        return $this->belongsTo('App\Modules\Branch\Models\BranchModel');
    }

    public function customer(){
        return $this->belongsTo('App\Modules\Customer\Models\CustomerModel');
    }

}
