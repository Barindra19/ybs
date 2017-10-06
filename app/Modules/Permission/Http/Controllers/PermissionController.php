<?php

namespace App\Modules\Permission\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use App\User;
use App\Modules\Permission\Models\Permission as Permission;
use Auth;
use Theme;
use Entrust;


class PermissionController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->_data['string_menuname']             = 'Permission';
        $this->_data['IDMENU']                      = 'Permission';
    }

    public function index(){
        $this->_data['string_active_menu'] = 'List';
        $this->_data['datatables']                  = 'permission';
        $this->_data['IDSUBMENU']                   = 'ListPermission';

        return Theme::view('modules.permission.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu'] = 'List';
        $this->_data['datatables']                  = 'permission';
        $this->_data['IDSUBMENU']                   = 'ListPermission';

        return Theme::view('modules.permission.show',$this->_data);
    }

    public function datatables(){
        $Permission = Permission::select(['id', 'name', 'display_name', 'description', 'created_at', 'updated_at']);

        return Datatables::of($Permission)
                    ->addColumn('href', function ($Permission) {
                        return '<a href="'.route('permission_edit',$Permission->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$Permission->id.')"><i class="glyphicon glyphicon-trash"></i></a>';
                    })
                    ->rawColumns(['href'])
                    // ->editColumn('id', 'ID: {{$id}}')
                    ->make(true);
    }
    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddPermission';

        return Theme::view('modules.permission.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddPermission';
        $this->_data['id']                          = $request->id;
         $Permission = Permission::find($request->id);
         $this->_data['Permission']                 = $Permission;

        return Theme::view('modules.permission.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'display_name' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
             return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $Permission                           = new Permission;
        $Permission->name               = $request->name;
        $Permission->display_name  = $request->display_name;
        $Permission->description      = $request->description;
        if($Permission->save()){
            return redirect()
            ->route('permission')
            ->with('scsMsg',"Configuration Permission success");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'display_name' => 'required',
            'description' => 'required'
        ]);

        if ($validator->fails()) {
             return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Permission                           = Permission::find($request->id);
        $Permission->name               = $request->name;
        $Permission->display_name  = $request->display_name;
        $Permission->description      = $request->description;
        if($Permission->save()){
            return redirect()
            ->route('permission')
            ->with('scsMsg',"Data succesfuly saving");
        }
    }

    public function delete(Request $request){

        $model_Permission           = new Permission();
        $Permission                 = Permission::find($request->id);
        if($Permission){
            if($Permission->delete()){
                return redirect()
                    ->route('permission')
                    ->with('scsMsg',"Data succesfuly deleted");
            }else{
                dd("Error deleted Data Permission");
            }
        }
    }
}
