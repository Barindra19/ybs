<?php

namespace App\Modules\Treatmentpackage\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentPackageModel extends Model
{
    protected $table = 'treatment_packages';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }
}
