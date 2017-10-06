<?php

namespace App\Modules\Merk\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Merk\Models\MerkModel;
use Auth;
use Theme;


class MerkController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:merk-view']);
        $this->middleware('permission:merk-add')->only('add');
        $this->middleware('permission:merk-edit')->only('edit');
        $this->middleware('permission:merk-inactive')->only('inactive');

        $this->_data['string_menuname']             = 'Merk';
        $this->_data['IDMENU']                      = 'Merk';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'merk';
        $this->_data['IDSUBMENU']                   = 'ListMerk';

        return Theme::view('modules.merk.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'merk';
        $this->_data['IDSUBMENU']                   = 'ListMerk';

        return Theme::view('modules.merk.show',$this->_data);
    }

    public function datatables(){
        $Merk = MerkModel::select(['id', 'name', 'is_active', 'created_at', 'updated_at']);

        return Datatables::of($Merk)
            ->addColumn('href', function ($Merk) {
                return '<a href="'.route('merk_edit',$Merk->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$Merk->id.')"><i class="fa fa-ban"></i></a>';
            })

            ->editColumn('is_active', function ($Merk) {
                if($Merk->is_active == 1){
                    return '<span class="label label-sm label-success">'.get_active($Merk->is_active).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_active($Merk->is_active).'</span>';
                }
            })

            ->rawColumns(['href','is_active'])
            ->make(true);
    }


    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddMerk';

        return Theme::view('modules.merk.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddMerk';

        $this->_data['id']                          = $request->id;
        $Merk                                       = MerkModel::find($request->id);

        $this->_data['Merk']                        = $Merk;

        return Theme::view('modules.merk.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Merk                                       = new MerkModel();
        $Merk->name                                 = $request->name;
        $Merk->created_by                           = Auth::id();
        $Merk->is_active                            = 1;

        if($Merk->save()){
            return redirect()
                ->route('merk_show')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $id                                         = $request->id;
        $Merk                                       = MerkModel::find($request->id);
        $Merk->name                                 = $request->name;
        $Merk->updated_by                           = Auth::id();
        $Merk->is_active                            = 1;

        if($Merk->save()){
            return redirect()
                ->route('merk_show')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function inactive(Request $request){
        $Merk                                       = MerkModel::find($request->id);
        $Merk->is_active                            = 0;
        $Merk->updated_by                           = Auth::id();
        if($Merk->save()){
            return redirect()
                ->route('merk_show')
                ->with('scsMsg',"Branch succesful inactive");

        }else{
            dd("Error data inactive");
        }
    }


}
