<?php

namespace App\Modules\Event\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }

    public function customer(){
        return $this->hasMany('App\Modules\Customer\Models\CustomerModel');
    }
    public function branch(){
        return $this->belongsTo('App\Modules\Branch\Models\BranchModel');
    }


}
