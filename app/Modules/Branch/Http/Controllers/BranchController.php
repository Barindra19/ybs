<?php

namespace App\Modules\Branch\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Branch\Models\BranchModel;
use Auth;
use Theme;

class BranchController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:branch-view']);
        $this->middleware('permission:branch-add')->only('add');
        $this->middleware('permission:branch-edit')->only('edit');
        $this->middleware('permission:branch-delete')->only('delete');

        $this->_data['string_menuname']             = 'Branch';
        $this->_data['IDMENU']                      = 'Branch';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'branch';
        $this->_data['IDSUBMENU']                   = 'ListBranch';

        return Theme::view('modules.branch.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'branch';
        $this->_data['IDSUBMENU']                   = 'ListBranch';
        $this->_data['DateReset']                   = date('d-m-Y');

        return Theme::view('modules.branch.show',$this->_data);
    }

    public function datatables(){
        $Branch = BranchModel::select(['id', 'name', 'address', 'phone', 'city','is_active', 'created_at', 'updated_at']);

        return Datatables::of($Branch)
            ->addColumn('href', function ($Branch) {
                return '<a href="'.route('branch_edit',$Branch->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$Branch->id.')"><i class="fa fa-ban"></i></a>&nbsp;
                        <a href="javascript:void(0);" class="btn btn-warning" onclick="ResetSaldo('.$Branch->id.')"><i class="glyphicon glyphicon-refresh"></i></a>&nbsp;&nbsp;
                        ';
            })

            ->editColumn('is_active', function ($Branch) {
                if($Branch->is_active == 1){
                    return '<span class="label label-sm label-success">'.get_active($Branch->is_active).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_active($Branch->is_active).'</span>';
                }
            })

            ->rawColumns(['href','address','is_active'])
            ->make(true);
    }


    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddBranch';

        return Theme::view('modules.branch.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddBranch';

        $this->_data['id']                          = $request->id;
        $Branch                                     = BranchModel::find($request->id);

        $this->_data['Branch']                      = $Branch;

        return Theme::view('modules.branch.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'address'   => 'required',
            'phone'     => 'required',
            'city'    => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Branch                                     = new BranchModel();
        $Branch->name                               = $request->name;
        $Branch->address                            = $request->address;
        $Branch->phone                              = $request->phone;
        $Branch->city                               = $request->city;
        $Branch->persentage                         = set_clearFormatRupiah($request->persentage);
        $Branch->saldo                              = 0;
        $Branch->saldo_start                        = date('Y-m-d H:i:s');
        $Branch->saldo_realtime                     = 0;
        $Branch->saldo_realtime_date                = date('Y-m-d H:i:s');
        $Branch->created_by                         = Auth::id();
        $Branch->is_active                          = 1;

        if($Branch->save()){
            return redirect()
                ->route('branch_show')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'address'   => 'required',
            'phone'     => 'required',
            'city'    => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $id                                         = $request->id;
        $Branch                                     = BranchModel::find($request->id);
        $Branch->name                               = $request->name;
        $Branch->address                            = $request->address;
        $Branch->phone                              = $request->phone;
        $Branch->city                               = $request->city;
        $Branch->persentage                         = set_clearFormatRupiah($request->persentage);
        $Branch->updated_by                         = Auth::id();
        $Branch->is_active                          = 1;

        if($Branch->save()){
            return redirect()
                ->route('branch_show')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function delete(Request $request){
        $Branch                                     = BranchModel::find($request->id);
        $Branch->is_active                          = 0;
        $Branch->updated_by                         = Auth::id();
        if($Branch->save()){
            return redirect()
                ->route('branch_show')
                ->with('scsMsg',"Branch succesful inactive");

        }else{
            dd("Error data inactive");
        }
    }

    public function resetsaldo(Request $request){
        $SaldoAwal                                  = set_clearFormatRupiah($request->saldoawal);
        $BranchID                                   = $request->id_reset;

        if($SaldoAwal == ""){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Saldo harus diisi.'
            );
        }else{
            $Branch                                 = BranchModel::find($BranchID);
            $Branch->saldo                          = $SaldoAwal;
            $Branch->saldo_start                    = DateFormat($request->date_reset,"Y-m-d H:i:s");
            $Branch->saldo_realtime                 = $SaldoAwal;
            $Branch->saldo_realtime_date            = DateFormat($request->date_reset,"Y-m-d H:i:s");
            if($Branch->save()){
                $data                                    = array(
                    "status"                                => true,
                    "message"                               => 'Data Saldo berhasil di reset'
                );
            }else{
                $data                                    = array(
                    "status"                                => false,
                    "message"                               => 'Ada kesalahan sistem. Mohon hubungi web developer. (13)'
                );
            }

        }
        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function info(Request $request){
        $BranchID                                   = $request->branch_id;
        $BranchInfo                                 = BranchModel::find($BranchID);

        if($BranchInfo){
            $data                                    = array(
                "status"                                => true,
                "message"                               => 'OK',
                "output"                                => array(
                    "branch"                            => $BranchInfo,
                    "saldo_realtime"                    => number_format($BranchInfo->saldo_realtime,0,",","."),
                    "saldo_realtime_date"               => DateFormat($BranchInfo->saldo_realtime_date,"d F Y H:i:s")
                )
            );

        }else{
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Ada kesalahan sistem. Mohon hubungi web developer. (14)'
            );
        }

        return response($data, 200)
        ->header('Content-Type', 'text/plain');


    }

}
