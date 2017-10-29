<?php

namespace App\Modules\Supplier\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Supplier\Models\Supplier as SupplierModel;
use App\Modules\Branch\Models\BranchModel;
use App\Modules\User\Models\UserModel;


use App\Modules\Role\Models\Role;
use App\Jobs\SendMail;
use Illuminate\Auth\Events\Registered;
use App\User;
use PDF;
use Auth;
use Theme;

class SupplierController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:supplier-view']);
        $this->middleware('permission:supplier-add')->only('add');
        $this->middleware('permission:supplier-edit')->only('edit');
        $this->middleware('permission:supplier-delete')->only('delete');

        $this->_data['string_menuname']             = 'Supplier';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'supplier';

        return Theme::view('modules.supplier.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'supplier';

        return Theme::view('modules.supplier.show',$this->_data);
    }

    public function datatables(){
        $Supplier = SupplierModel::select(['id','name','bank','account_bank','phone'])
            ->where('is_active','=',1);

        return Datatables::of($Supplier)
            ->addColumn('href', function ($Supplier) {
                return '<a href="'.route('supplier_edit',$Supplier->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$Supplier->id.')"><i class="glyphicon glyphicon-trash"></i></a>
                        ';
            })
            ->rawColumns(['href'])
            ->make(true);
    }

    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';

        $Users                                      = Auth::user();

        return Theme::view('modules.supplier.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'  => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Supplier                                   = new SupplierModel();
        $Supplier->name                             = $request->name;
        $Supplier->address                          = $request->address;
        $Supplier->phone                            = $request->phone;
        $Supplier->bank                             = $request->bank;
        $Supplier->account_bank                     = $request->account_bank;
        $Supplier->note                             = $request->note;
        $Supplier->is_active                        = 1;
        $Supplier->created_by                       = Auth::id();

        if($Supplier->save()){
            return redirect()
                ->route('supplier_show')
                ->with('scsMsg',"Data successfuly saving");
        }
    }


    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';

        $this->_data['id']                          = $request->id;
        $Supplier                                   = SupplierModel::find($request->id);

        $this->_data['Supplier']                    = $Supplier;


        return Theme::view('modules.supplier.form',$this->_data);
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $id                                         = $request->id;

        $Supplier                                   = SupplierModel::find($id);
        $Supplier->name                             = $request->name;
        $Supplier->address                          = $request->address;
        $Supplier->phone                            = $request->phone;
        $Supplier->bank                             = $request->bank;
        $Supplier->account_bank                     = $request->account_bank;
        $Supplier->note                             = $request->note;
        $Supplier->is_active                        = 1;
        $Supplier->created_by                       = Auth::id();

        if($Supplier->save()){
            return redirect()
                ->route('supplier_show')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function delete(Request $request){
        $Supplier                                   = SupplierModel::find($request->id);
        $Supplier->is_active                        = 0;
        $Supplier->updated_by                       = Auth::id();
        if($Supplier){
            if($Supplier->save()){
                return redirect()
                    ->route('supplier_show')
                    ->with('scsMsg',"Data succesfuly deleted");
            }else{
                dd("Error deleted Data Customer");
            }
        }
    }

    public function add_supplier(Request $request){

        $Name                       = $request->name;
        $Address                    = $request->address;
        $Phone                      = $request->phone;
        $Bank                       = $request->bank;
        $AccountBank                = $request->account_bank;
        $Note                       = $request->note;

        if($Name == ""){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Nama wajib diisi.',
                "field"                                 => 'name'
            );
            return response($data, 200)
                ->header('Content-Type', 'text/plain');

        }else{
            $Users                                      = Auth::user();
            $Supplier                                   = new SupplierModel();
            $Supplier->name                             = $request->name;
            $Supplier->address                          = $request->address;
            $Supplier->phone                            = $request->phone;
            $Supplier->bank                             = $request->bank;
            $Supplier->account_bank                     = $request->account_bank;
            $Supplier->note                             = $request->note;
            $Supplier->is_active                        = 1;
            $Supplier->created_by                       = Auth::id();

            if($Supplier->save()){

                $data                                    = array(
                    "status"                                => true,
                    "message"                               => 'Supplier berhasil ditambahkan',
                    "output"                                => $Supplier,
                    "supplier_select"                       => '<option value="'.$Supplier->id.'">'.$Supplier->name.'</option>'
                );
                return response($data, 200)
                    ->header('Content-Type', 'text/plain');
            }
        }


    }


}
