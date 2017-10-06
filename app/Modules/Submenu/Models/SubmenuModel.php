<?php

namespace App\Modules\Submenu\Models;

use Illuminate\Database\Eloquent\Model;

class SubmenuModel extends Model
{
    protected $table = 'submenus';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }
}
