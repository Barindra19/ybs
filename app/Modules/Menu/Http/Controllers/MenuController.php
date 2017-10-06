<?php

namespace App\Modules\Menu\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Menu\Models\MenuModel;
use App\Modules\Role\Models\Role;
use App\Modules\Permission\Models\Permission as Permission;
use Auth;
use Theme;


class MenuController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:menu-view']);
        $this->middleware('permission:menu-add')->only('add');
        $this->middleware('permission:menu-edit')->only('edit');
        $this->middleware('permission:menu-delete')->only('delete');

        $this->_data['string_menuname']             = 'Menu';
        $this->_data['IDMENU']                      = 'Menu';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'menu';
        $this->_data['IDSUBMENU']                   = 'ListMenu';

        return Theme::view('modules.menu.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'menu';
        $this->_data['IDSUBMENU']                   = 'ListMenu';

        return Theme::view('modules.menu.show',$this->_data);
    }

    public function datatables(){
        $Menu = MenuModel::select(['id', 'name', 'url', 'permission', 'icon', 'created_at', 'updated_at']);

        return Datatables::of($Menu)
            ->addColumn('href', function ($Menu) {
                return '<a href="'.route('menu_edit',$Menu->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$Menu->id.')"><i class="glyphicon glyphicon-trash"></i></a>';
            })
            ->editColumn('icon', '<i class="{{$icon}}"></i>')
            ->rawColumns(['href','icon'])
            ->make(true);
    }

    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddMenu';

        $Permission                                 = Permission::all();
        $this->_data['Permissions']                 = $Permission;
        return Theme::view('modules.menu.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddMenu';

        $this->_data['id']                          = $request->id;
        $Menu                                       = MenuModel::find($request->id);
        $Permission                                 = Permission::all();

        $this->_data['Menu']                        = $Menu;
        $this->_data['Permissions']                 = $Permission;

        return Theme::view('modules.menu.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'permission' => 'required'
        ]);

        $Permission = Permission::all();
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Menu                                   = new MenuModel;
        $Menu->name                             = $request->name;
        $Menu->url                              = $request->url;
        $Menu->permission                       = $request->permission;
        $Menu->icon                             = $request->icon;

        if($Menu->save()){
            return redirect()
                ->route('menu')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'permission' => 'required'
        ]);

        $Permission                     = Permission::all();

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $id                             = $request->id;
        $Menu                           = MenuModel::find($request->id);
        $Menu->name                     = $request->name;
        $Menu->url                      = $request->url;
        $Menu->permission               = $request->permission;
        $Menu->icon               = $request->icon;

        if($Menu->save()){
            return redirect()
                ->route('menu')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function delete(Request $request){

        $Menu                       = MenuModel::find($request->id);
        if($Menu){
            if($Menu->delete()){
                return redirect()
                    ->route('menu')
                    ->with('scsMsg',"Data succesfuly deleted");

            }else{
                dd("Error deleted Data Role");
            }
        }
    }


}
