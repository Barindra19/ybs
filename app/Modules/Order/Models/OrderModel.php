<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{

    protected $table = 'orders';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }

    public function customer(){
        return $this->belongsTo('App\Modules\Customer\Models\CustomerModel');
    }

}
