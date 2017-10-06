<?php

namespace App\Modules\Cashbook\Models;

use Illuminate\Database\Eloquent\Model;

class CashBookModel extends Model
{
    protected $table = 'cashbooks';
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

    public function status(){
        return $this->belongsTo('App\Modules\Status\Models\StatusModel');
    }
}
