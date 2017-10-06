<?php
/**
* @author Barindra Maslo
*/

namespace App\Modules\Role\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use App\User;
use App\Modules\Role\Models\Role as Role;
use App\Modules\Permission\Models\Permission as Permission;
use App\Modules\Permission\Models\PermissionRole as PermissionRole;
use Auth;
use Theme;
use Entrust;

class RoleController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:role-view']);
        $this->middleware('permission:role-add')->only('add');
        $this->middleware('permission:role-edit')->only('edit');
        $this->middleware('permission:role-delete')->only('delete');


        $this->_data['string_menuname']                 = 'Role';
        $this->_data['IDMENU']                          = 'Role';
    }

    public function index(){
        $this->_data['string_active_menu']              = 'List';
        $this->_data['datatables']                      = 'role';
        $this->_data['IDSUBMENU']                       = 'ListRole';
        // dd(Datatables::eloquent(Role::query())
        // ->make(true)
        // );
        return Theme::view('modules.role.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']              = 'List';
        $this->_data['datatables']                      = 'role';
        $this->_data['IDSUBMENU']                       = 'ListRole';
        // dd(Datatables::eloquent(Role::query())
        // ->make(true)
        // );
        return Theme::view('modules.role.show',$this->_data);
    }

    public function datatables(){
        $Role = Role::select(['id', 'name', 'display_name', 'description', 'created_at', 'updated_at']);

        return Datatables::of($Role)
                    ->addColumn('href', function ($Role) {
                        return '
                        <a href="'.route('role_edit',$Role->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$Role->id.')"><i class="glyphicon glyphicon-trash"></i></a>';
                    })
                    ->rawColumns(['href'])
                    ->withTrashed()
                    ->make(true);
    }

    public function add(){
        $this->_data['state']                           = 'add';
        $this->_data['string_active_menu']              = 'Add/Edit';
        $this->_data['IDSUBMENU']                       = 'AddRole';

        $Permission = Permission::all();
        $this->_data['Permissions'] = $Permission;

        return Theme::view('modules.role.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                           = 'edit';
        $this->_data['string_active_menu']              = 'Add/Edit';
        $this->_data['IDSUBMENU']                       = 'AddRole';
        $this->_data['id']                              = $request->id;
         $Role                                          = Role::find($request->id);
         $Permission                                    = Permission::all();

         $model_PermissionRole                          = new PermissionRole();
         $array_PermissionRole                          = $model_PermissionRole->get_listPermissionRole($request->id);

         $this->_data['Role']                           = $Role;
         $this->_data['Permissions']                    = $Permission;
         $this->_data['listPermissions']                = $array_PermissionRole;

        //  dd($PermissionRole);

        return Theme::view('modules.role.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'display_name' => 'required',
            'description' => 'required'
        ]);

        $Permission = Permission::all();

        if ($validator->fails()) {
             return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Role                           = new Role;
        $Role->name               = $request->name;
        $Role->display_name  = $request->display_name;
        $Role->description      = $request->description;

        if($Role->save()){
            if($Permission){
                foreach($Permission as $item){
                    $int_PermissionID       = $item->id;
                    $obj                               = 'access'.$int_PermissionID;
                    if($request->$obj){
                        $Access                         = $request->$obj;
                        $Role->attachPermission($Access);
                    }
                }
            }
            return redirect()
            ->route('role')
            ->with('scsMsg',"Configuration Role success");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'display_name' => 'required',
            'description' => 'required'
        ]);

        $Permission                     = Permission::all();
        $model_PermissionRole           = new PermissionRole();


        if ($validator->fails()) {
             return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $id                             = $request->id;
        $Role                           = Role::find($request->id);
        $Role->name                     = $request->name;
        $Role->display_name             = $request->display_name;
        $Role->description              = $request->description;
        if($Role->save()){
            $set_deleted                = $model_PermissionRole->set_deleted('role_id',$request->id);
                    if($Permission){
                        foreach($Permission as $item){
                            $int_PermissionID       = $item->id;
                            $obj                               = 'access'.$int_PermissionID;
                            if($request->$obj){
                                $Access                         = $request->$obj;
                                $Role->attachPermission($Access);
                            }
                        }
                    }
            return redirect()
            ->route('role')
            ->with('message',"Data succesfuly saving");
        }
    }

    public function delete(Request $request){

        $model_PermissionRole       = new PermissionRole();
        $Role                       = Role::find($request->id);
        if($Role){
            if($Role->delete()){
                $set_deleted                = $model_PermissionRole->set_deleted('role_id',$request->id);
                return redirect()
                    ->route('role')
                    ->with('scsMsg',"Data succesfuly deleted");

            }else{
                dd("Error deleted Data Role");
            }
        }
    }
}
