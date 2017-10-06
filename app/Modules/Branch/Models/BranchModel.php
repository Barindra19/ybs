<?php

namespace App\Modules\Branch\Models;

use Illuminate\Database\Eloquent\Model;

class BranchModel extends Model
{
    protected $table = 'branchs';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }

    public function customer(){
        return $this->hasMany('App\Modules\Customer\Models\CustomerModel');
    }
}
