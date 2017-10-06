<?php

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetailModel extends Model
{
    protected $table = 'order_details';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }

    public function merk(){
        return $this->belongsTo('App\Modules\Merk\Models\MerkModel');
    }

    public function treatment(){
        return $this->belongsTo('App\Modules\Treatment\Models\TreatmentModel');
    }

    public function treatmentcategory(){
        return $this->belongsTo('App\Modules\Treatmentcategory\Models\TreatmentCategoryModel','treatment_category_id','id');
    }

    public function treatmentpackage(){
        return $this->belongsTo('App\Modules\Treatmentpackage\Models\TreatmentPackageModel','treatment_package_id','id');
    }


}
