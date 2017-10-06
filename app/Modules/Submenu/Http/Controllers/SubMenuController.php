<?php

namespace App\Modules\Submenu\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use App\Modules\Menu\Models\MenuModel;
use App\Modules\Submenu\Models\SubmenuModel;
use App\Modules\Permission\Models\Permission as Permission;
use Auth;
use Theme;


class SubMenuController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:submenu-view']);
        $this->middleware('permission:submenu-add')->only('add');
        $this->middleware('permission:submenu-edit')->only('edit');
        $this->middleware('permission:submenu-delete')->only('delete');

        $this->_data['string_menuname']             = 'Sub Menu';
        $this->_data['IDMENU']                      = 'Submenu';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'submenu';
        $this->_data['IDSUBMENU']                   = 'ListSubmenu';

        return Theme::view('modules.submenu.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'submenu';
        $this->_data['IDSUBMENU']                   = 'ListSubmenu';

        return Theme::view('modules.submenu.show',$this->_data);
    }

    public function datatables(){
        $Submenu = SubmenuModel::join('menus','submenus.parent_id', '=', 'menus.id')
                            ->select(['submenus.id', 'menus.name as parent', 'submenus.name', 'submenus.url', 'submenus.permission', 'submenus.icon', 'submenus.created_at', 'submenus.updated_at']);

        return Datatables::of($Submenu)
            ->addColumn('href', function ($Submenu) {
                return '<a href="'.route('submenu_edit',$Submenu->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$Submenu->id.')"><i class="glyphicon glyphicon-trash"></i></a>';
            })
            ->editColumn('icon', '<i class="{{$icon}}"></i>')
            ->rawColumns(['href','icon'])
            ->make(true);
    }

    public function add(){
        $this->_data['state']                   = 'add';
        $this->_data['string_active_menu']      = 'Add/Edit';
        $this->_data['IDSUBMENU']               = 'AddSubmenu';

        $Permission                             = Permission::all();
        $Menu                                   = MenuModel::all();
        $this->_data['Permissions']             = $Permission;
        $this->_data['Parents']                 = $Menu;
        return Theme::view('modules.submenu.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                   = 'edit';
        $this->_data['string_active_menu']      = 'Add/Edit';
        $this->_data['IDSUBMENU']               = 'AddSubmenu';

        $this->_data['id']                      = $request->id;
        $Permission                             = Permission::all();
        $SubMenu                                = SubmenuModel::find($request->id);
        $Menu                                   = MenuModel::find($SubMenu->parent_id);

        $this->_data['Submenu']                 = $SubMenu;
        $this->_data['Menu']                    = $Menu;

        $arr_Menu                               = MenuModel::all();
        $this->_data['Parents']                 = $arr_Menu;
        $this->_data['Permissions']             = $Permission;

        return Theme::view('modules.submenu.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'parent'        => 'required',
            'url'           => 'required',
            'permission'    => 'required'
        ]);

        $Permission                             = Permission::all();
        $Menu                                   = MenuModel::all();
        $this->_data['Permissions']             = $Permission;
        $this->_data['Parents']                 = $Menu;
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $SubMenu                                   = new SubmenuModel();
        $SubMenu->name                             = $request->name;
        $SubMenu->parent_id                        = $request->parent;
        $SubMenu->url                              = $request->url;
        $SubMenu->permission                       = $request->permission;
        $SubMenu->icon                             = $request->icon;

        if($SubMenu->save()){
            return redirect()
                ->route('submenu')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'url' => 'required',
            'permission' => 'required'
        ]);

        $Permission                             = Permission::all();
        $Menu                                   = MenuModel::all();
        $this->_data['Permissions']             = $Permission;
        $this->_data['Parents']                 = $Menu;

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $id                                         = $request->id;
        $SubMenu                                    = SubmenuModel::find($request->id);
        $SubMenu->name                              = $request->name;
        $SubMenu->parent_id                         = $request->parent;
        $SubMenu->url                               = $request->url;
        $SubMenu->permission                        = $request->permission;
        $SubMenu->icon                              = $request->icon;

        if($SubMenu->save()){
            return redirect()
                ->route('submenu')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function delete(Request $request){

        $SubMenu                       = SubmenuModel::find($request->id);
        if($SubMenu){
            if($SubMenu->delete()){
                return redirect()
                    ->route('submenu')
                    ->with('scsMsg',"Data succesfuly deleted");

            }else{
                dd("Error deleted Data Role");
            }
        }
    }


}
