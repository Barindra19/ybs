<?php

namespace App\Modules\Treatmentcategory\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Treatmentcategory\Models\TreatmentCategoryModel;
use Auth;
use Theme;


class TreatmentCategoryController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:treatment-category-view']);
        $this->middleware('permission:treatment-category-add')->only('add');
        $this->middleware('permission:treatment-category-edit')->only('edit');
        $this->middleware('permission:treatment-category-inactive')->only('inactive');

        $this->_data['string_menuname']             = 'Treatment Category';
        $this->_data['IDMENU']                      = 'Treatment Category';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'treatmentcategory';
        $this->_data['IDSUBMENU']                   = 'ListTreatmentCategory';

        return Theme::view('modules.treatmentcategory.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'treatmentcategory';
        $this->_data['IDSUBMENU']                   = 'ListTreatmentCategory';

        return Theme::view('modules.treatmentcategory.show',$this->_data);
    }

    public function datatables(){
        $TreatmentCategory = TreatmentCategoryModel::join('treatments','treatments.id','=','treatment_categorys.treatment_id')
        ->select(['treatment_categorys.id as id', 'treatment_categorys.name', 'treatments.name as treatment_name', 'treatment_categorys.description', 'treatment_categorys.is_active', 'treatment_categorys.created_at', 'treatment_categorys.updated_at']);

        return Datatables::of($TreatmentCategory)
            ->addColumn('href', function ($TreatmentCategory) {
                return '<a href="'.route('treatmentcategory_edit',$TreatmentCategory->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$TreatmentCategory->id.')"><i class="fa fa-ban"></i></a>';
            })
            ->editColumn('is_active', function ($TreatmentCategory) {
                if($TreatmentCategory->is_active == 1){
                    return '<span class="label label-sm label-success">'.get_active($TreatmentCategory->is_active).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_active($TreatmentCategory->is_active).'</span>';
                }
            })

            ->rawColumns(['href','is_active'])
            ->make(true);
    }


    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddTreatmentCategory';

        return Theme::view('modules.treatmentcategory.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddTreatmentCategory';

        $this->_data['id']                          = $request->id;
        $TreatmentCategory                          = TreatmentCategoryModel::find($request->id);

        $this->_data['TreatmentCategory']           = $TreatmentCategory;

        return Theme::view('modules.treatmentcategory.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'treatment'     => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $TreatmentCategory                                      = new TreatmentCategoryModel();
        $TreatmentCategory->name                                = $request->name;
        $TreatmentCategory->description                         = $request->description;
        $TreatmentCategory->treatment_id                        = $request->treatment;
        $TreatmentCategory->created_by                          = Auth::id();
        $TreatmentCategory->is_active                           = 1;

        if($TreatmentCategory->save()){
            return redirect()
                ->route('treatmentcategory_show')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'treatment'     => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $id                                             = $request->id;
        $TreatmentCategory                              = TreatmentCategoryModel::find($request->id);
        $TreatmentCategory->name                        = $request->name;
        $TreatmentCategory->description                 = $request->description;
        $TreatmentCategory->treatment_id                = $request->treatment;
        $TreatmentCategory->updated_by                  = Auth::id();
        $TreatmentCategory->is_active                   = 1;

        if($TreatmentCategory->save()){
            return redirect()
                ->route('treatmentcategory_show')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function inactive(Request $request){
        $TreatmentCategory                              = TreatmentCategoryModel::find($request->id);
        $TreatmentCategory->is_active                   = 0;
        $TreatmentCategory->updated_by                  = Auth::id();
        if($Treatment->save()){
            return redirect()
                ->route('treatmentcategory_show')
                ->with('scsMsg',"Treatment Category succesful inactive");

        }else{
            dd("Error data inactive");
        }
    }

    public function searchbyparent(Request $request){
        $TreatmentID                                    = $request->treatment_id;
        $Where                                          = array(
            "is_active"                                 => 1,
            "treatment_id"                              => $TreatmentID
        );
        $TreatmentCategory                              = TreatmentCategoryModel::where($Where)->get();

            echo '<option value="0">Choose Treatment Category</option>';
        foreach($TreatmentCategory as $item){
            echo '<option value="'.$item->id.'">' . $item->name . '</option>';
        }
    }
}
