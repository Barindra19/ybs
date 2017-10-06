<?php

namespace App\Modules\Invoice\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceModel extends Model
{
    protected $table = 'invoices';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }

    public function customer(){
        return $this->belongsTo('App\Modules\Customer\Models\CustomerModel');
    }

    public function order(){
        return $this->belongsTo('App\Modules\Order\Models\OrderModel');
    }

    public function payment_type(){
        return $this->belongsTo('App\Payment_type');
    }

}
