<?php

namespace App\Modules\Treatmentpackage\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Treatmentpackage\Models\TreatmentPackageModel;
use Auth;
use Theme;

class TreatmentPackageController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:treatment-package-view']);
        $this->middleware('permission:treatment-package-add')->only('add');
        $this->middleware('permission:treatment-package-edit')->only('edit');
        $this->middleware('permission:treatment-package-inactive')->only('inactive');

        $this->_data['string_menuname']             = 'Treatment Package';
        $this->_data['IDMENU']                      = 'Treatment Package';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'treatmentpackage';
        $this->_data['IDSUBMENU']                   = 'ListTreatmentPackage';

        return Theme::view('modules.treatmentpackage.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'treatmentpackage';
        $this->_data['IDSUBMENU']                   = 'ListTreatmentPackage';

        return Theme::view('modules.treatmentpackage.show',$this->_data);
    }

    public function datatables(){
        $TreatmentPackage = TreatmentPackageModel::join('treatments','treatments.id','=','treatment_packages.treatment_id')
                                                    ->select(['treatment_packages.id as id', 'treatment_packages.name', 'treatments.name as treatment_name', 'treatment_packages.treatment_category_id as treatment_category', 'treatment_packages.price', 'treatment_packages.is_active', 'treatment_packages.created_at', 'treatment_packages.updated_at']);

        return Datatables::of($TreatmentPackage)
            ->addColumn('href', function ($TreatmentPackage) {
                return '<a href="'.route('treatmentpackage_edit',$TreatmentPackage->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$TreatmentPackage->id.')"><i class="fa fa-ban"></i></a>';
            })
            ->editColumn('is_active', function ($TreatmentPackage) {
                if($TreatmentPackage->is_active == 1){
                    return '<span class="label label-sm label-success">'.get_active($TreatmentPackage->is_active).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_active($TreatmentPackage->is_active).'</span>';
                }
            })

            ->editColumn('treatment_category', '{{ get_categoryByID($treatment_category,"name") }}')
            ->editColumn('price', '{{ number_format($price,0,",",".") }}')
            ->rawColumns(['href','is_active'])
            ->make(true);
    }


    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddTreatmentPackage';

        return Theme::view('modules.treatmentpackage.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddTreatmentCategory';

        $this->_data['id']                          = $request->id;
        $TreatmentPackage                           = TreatmentPackageModel::find($request->id);

        $this->_data['TreatmentPackage']            = $TreatmentPackage;

        return Theme::view('modules.treatmentpackage.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'treatment'     => 'required',
            'price'         => 'required',
            'point'         => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $TreatmentPackage                                       = new TreatmentPackageModel();
        $TreatmentPackage->name                                 = $request->name;
        $TreatmentPackage->description                          = $request->description;
        $TreatmentPackage->treatment_id                         = $request->treatment;
        $TreatmentPackage->treatment_category_id                = $request->treatmentcategory;
        $TreatmentPackage->price                                = set_clearFormatRupiah($request->price);
        $TreatmentPackage->point                                = $request->point;
        $TreatmentPackage->created_by                           = Auth::id();
        $TreatmentPackage->is_active                            = 1;

        if($TreatmentPackage->save()){
            return redirect()
                ->route('treatmentpackage_show')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'treatment'     => 'required',
            'price'         => 'required',
            'point'         => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $id                                                     = $request->id;
        $TreatmentPackage                                       = TreatmentPackageModel::find($request->id);
        $TreatmentPackage->name                                 = $request->name;
        $TreatmentPackage->description                          = $request->description;
        $TreatmentPackage->treatment_id                         = $request->treatment;
        $TreatmentPackage->treatment_category_id                = $request->treatmentcategory;
        $TreatmentPackage->price                                = set_clearFormatRupiah($request->price);
        $TreatmentPackage->point                                = $request->point;
        $TreatmentPackage->updated_by                           = Auth::id();
        $TreatmentPackage->is_active                            = 1;

        if($TreatmentPackage->save()){
            return redirect()
                ->route('treatmentpackage_show')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function inactive(Request $request){
        $TreatmentPackage                                       = TreatmentPackageModel::find($request->id);
        $TreatmentPackage->is_active                            = 0;
        $TreatmentPackage->updated_by                           = Auth::id();
        if($TreatmentPackage->save()){
            return redirect()
                ->route('treatmentpackage_show')
                ->with('scsMsg',"Treatment Package succesful inactive");

        }else{
            dd("Error data inactive");
        }
    }

    public function searchbytreatment(Request $request){
        $TreatmentID                                    = $request->treatment_id;
        $Where                                          = array(
            "is_active"                                 => 1,
            "treatment_id"                              => $TreatmentID
        );
        $TreatmentPackage                               = TreatmentPackageModel::where($Where)->get();

            echo '<option value="0">Choose Treatment Package</option>';
        foreach($TreatmentPackage as $item){
            echo '<option value="'.$item->id.'">' . $item->name . '</option>';
        }
    }

    public function searchbycategory(Request $request){
        $TreatmentID                                    = $request->treatment_id;
        $CategoryID                                     = $request->category_id;

        $Where                                          = array(
            "is_active"                                 => 1,
            "treatment_category_id"                     => $CategoryID,
            "treatment_id"                              => $TreatmentID
        );
        $TreatmentPackage                               = TreatmentPackageModel::where($Where)->get();

            echo '<option value="0">Choose Treatment Package</option>';
        foreach($TreatmentPackage as $item){
            echo '<option value="'.$item->id.'">' . $item->name . '</option>';
        }
    }

    public function getdetailpackage(Request $request){
        $PackageID                                      = $request->package_id;

        $TreatmentPackage                               = TreatmentPackageModel::find($PackageID);

        if($TreatmentPackage){
            $data                                    = array(
                "status"                                => true,
                "output"                                => array(
                    "price"                             => $TreatmentPackage->price,
                    "price_display"                     => number_format($TreatmentPackage->price,0,",","."),
                    "description"                       => $TreatmentPackage->description,
                    "name"                              => $TreatmentPackage->name
                )
            );
        }else{
            $data                                    = array(
                "status"                                => false,
                "output"                                => 'Customer Not Found'
            );
        }

    return response($data, 200)
        ->header('Content-Type', 'text/plain');

    }
}
