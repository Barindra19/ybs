<?php

namespace App\Modules\Treatment\Models;

use Illuminate\Database\Eloquent\Model;

class TreatmentModel extends Model
{
    protected $table = 'treatments';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }
}
