<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;

class OrderImageModel extends Model
{
    protected $table = 'order_images';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }

    public function order_detail(){
        return $this->belongsTo('App\Modules\Order\Models\OrderDetailModel');
    }


}
