<?php

namespace App\Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerModel extends Model
{
    protected $table = 'customers';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }

    public function branch(){
        return $this->belongsTo('App\Modules\Branch\Models\BranchModel');
    }

    public function order(){
        return $this->hasMany('App\Modules\Order\Models\OrderModel');
    }


}
