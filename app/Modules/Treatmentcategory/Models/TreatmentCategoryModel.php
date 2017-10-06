<?php

namespace App\Modules\Treatmentcategory\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentCategoryModel extends Model
{
    protected $table = 'treatment_categorys';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }
}
