<?php

namespace App\Modules\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PermissionRole extends Model
{
    /**
         * The table associated with the model.
         *
         * @var string
         * @author Barindra
         */

    protected $table = 'permission_role';
    protected $connection;

    public function __construct(array $attributes = [])
    {
        $this->connection = env('DB_CONNECTION', 'mysql');
        parent::__construct($attributes);
    }

    public function get_listPermissionRole($role_id){
        $permission_role = DB::connection($this->connection)->table('permission_role')
                     ->select('permission_id', 'role_id')
                     ->where('role_id', '=', $role_id)
                     ->get();
        return $permission_role;
    }

    public function set_deleted($field,$value){
        $execute            =     DB::connection($this->connection)->table('permission_role')
                                        ->where($field,$value)
                                        ->delete();
        return $execute;
    }

    public function get_ListPermissionDetail($role_id){
        $permission_role = DB::connection($this->connection)->table('permission_role')
            ->select('permission_role.permission_id', 'permissions.*')
            ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
            ->where('permission_role.role_id', '=', $role_id)
            ->get();
        return $permission_role;
    }
}
