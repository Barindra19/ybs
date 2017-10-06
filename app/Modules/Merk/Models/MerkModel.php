<?php

namespace App\Modules\Merk\Models;

use Illuminate\Database\Eloquent\Model;

class MerkModel extends Model
{
    protected $table = 'merks';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }


}
