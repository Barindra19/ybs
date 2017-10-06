<?php

namespace App\Modules\Treatment\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Treatment\Models\TreatmentModel;
use Auth;
use Theme;


class TreatmentController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:treatment-view']);
        $this->middleware('permission:treatment-add')->only('add');
        $this->middleware('permission:treatment-edit')->only('edit');
        $this->middleware('permission:treatment-inactive')->only('inactive');

        $this->_data['string_menuname']             = 'Treatment';
        $this->_data['IDMENU']                      = 'Treatment';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'treatment';
        $this->_data['IDSUBMENU']                   = 'ListTreatment';

        return Theme::view('modules.treatment.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'treatment';
        $this->_data['IDSUBMENU']                   = 'ListTreatment';

        return Theme::view('modules.treatment.show',$this->_data);
    }

    public function datatables(){
        $Treatment = TreatmentModel::select(['id', 'name', 'description', 'is_active', 'created_at', 'updated_at']);

        return Datatables::of($Treatment)
            ->addColumn('href', function ($Treatment) {
                return '<a href="'.route('treatment_edit',$Treatment->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$Treatment->id.')"><i class="fa fa-ban"></i></a>';
            })

            ->editColumn('is_active', function ($Treatment) {
                if($Treatment->is_active == 1){
                    return '<span class="label label-sm label-success">'.get_active($Treatment->is_active).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_active($Treatment->is_active).'</span>';
                }
            })

            // ->editColumn('is_active', '{{ get_active($is_active) }}')
            ->rawColumns(['href','is_active'])
            ->make(true);
    }


    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddTreatment';

        return Theme::view('modules.treatment.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddTreatment';

        $this->_data['id']                          = $request->id;
        $Treatment                                  = TreatmentModel::find($request->id);

        $this->_data['Treatment']                   = $Treatment;

        return Theme::view('modules.treatment.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Treatment                                      = new TreatmentModel();
        $Treatment->name                                = $request->name;
        $Treatment->description                         = $request->description;
        $Treatment->created_by                          = Auth::id();
        $Treatment->is_active                           = 1;

        if($Treatment->save()){
            return redirect()
                ->route('treatment_show')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $id                                             = $request->id;
        $Treatment                                      = TreatmentModel::find($request->id);
        $Treatment->name                                = $request->name;
        $Treatment->description                         = $request->description;
        $Treatment->updated_by                          = Auth::id();
        $Treatment->is_active                           = 1;

        if($Treatment->save()){
            return redirect()
                ->route('treatment_show')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function inactive(Request $request){
        $Treatment                                      = TreatmentModel::find($request->id);
        $Treatment->is_active                           = 0;
        $Treatment->updated_by                          = Auth::id();
        if($Treatment->save()){
            return redirect()
                ->route('treatment_show')
                ->with('scsMsg',"Treatment succesful inactive");

        }else{
            dd("Error data inactive");
        }
    }
}
