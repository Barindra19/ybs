<?php

namespace App\Modules\Archive\Models;

use Illuminate\Database\Eloquent\Model;

class ArchiveOrder extends Model
{

    protected $table = 'archive_orders';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }

}
