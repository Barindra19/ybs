<?php

namespace App\Modules\Stock\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Stock\Models\Stock as StockModel;
use App\Modules\User\Models\UserModel;
use App\Modules\Role\Models\Role;
use App\User;

use App\Modules\Branch\Models\BranchModel;
use Auth;
use Theme;
use PDF;

use \Milon\Barcode\DNS1D;


use App\Jobs\SendMail;
use Illuminate\Auth\Events\Registered;



class StockController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:stock-view']);
        $this->middleware('permission:stock-add')->only('add');
        $this->middleware('permission:stock-edit')->only('edit');

        $this->_data['string_menuname']             = 'Stock Barang';
        $this->_data['IDMENU']                      = 'Stock Barang';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'stock';
        $this->_data['IDSUBMENU']                   = 'ListStock';

        return Theme::view('modules.stock.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'stock';
        $this->_data['IDSUBMENU']                   = 'ListStock';

        return Theme::view('modules.stock.show',$this->_data);
    }

    public function datatables(){
        $Users                                      = Auth::user();
        $BranchID                                   = $Users->branch_id;
        if($Users->can('access-pusat')){
            $Stock = StockModel::join('branchs','branchs.id','=','stocks.branch_id')
                                        ->select(['stocks.id', 'stocks.code', 'stocks.name', 'stocks.stock', 'stocks.selling_price', 'branchs.name as branch', 'stocks.restock_date as restock_date'])
                                        ->where('stocks.is_active','=',1);

        }else{
            $Stock = StockModel::join('branchs','branchs.id','=','stocks.branch_id')
                                        ->select(['stocks.id', 'stocks.code', 'stocks.name', 'stocks.stock', 'stocks.selling_price', 'branchs.name as branch', 'stocks.restock_date as restock_date'])
                                        ->where('stocks.branch_id','=',$BranchID)
                                        ->where('stocks.is_active','=',1);
        }

        return Datatables::of($Stock)
            ->editColumn('selling_price', '{{ number_format($selling_price,0,",",".") }}')
            ->editColumn('restock_date', '{{ DateFormat($restock_date,"d-m-Y H:i:s") }}')
            ->addColumn('href', function ($Stock) {
                $Btn                = '<a href="'.route('stock_edit',$Stock->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="inactiveList('.$Stock->id.')"><i class="fa fa-ban"></i></a>
                        <a href="javascript:void(0);" class="btn yellow" onclick="EditStock('.$Stock->id.')"><i class="fa fa-exchange"></i></a>';
                if($Stock->stock > 0){
                    $Btn                .= '                
                        <a href="'.route('stock_generate_barcode',$Stock->id).'" class="btn btn-success" target="_blank"><i class="fa fa-barcode"></i></a>
                        ';
                }
                return $Btn;
            })
            // ->editColumn('icon', '<i class="{{$icon}}"></i>')
            ->rawColumns(['href'])
            ->make(true);
    }

    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddStock';

        $Users                                      = Auth::user();

        $BranchID                                   = $Users->branch_id;
        $this->_data['BranchID']                    = $BranchID;
        $this->_data['DateNow']                     = date('d-m-Y');

        return Theme::view('modules.stock.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddStock';

        $this->_data['id']                          = $request->id;
        $Stock                                      = StockModel::find($request->id);
        $Users                                      = Auth::user();

        $BranchID                                   = $Users->branch_id;
        $this->_data['BranchID']                    = $BranchID;

        $this->_data['DateNow']                     = DateFormat($Stock->restock_date,"d-m-Y");

        $this->_data['Stock']                       = $Stock;

        return Theme::view('modules.stock.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'                      => 'required',
            'stock'                     => 'required',
            'cost_of_good'              => 'required',
            'selling_price'             => 'required',
            'supplier'                  => 'required',
            'brand'                     => 'required',
            'name_of_consignment'       => 'required',
            'restock_date'              => 'required',
            'point'                     => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Users                                      = Auth::user();

        $Stock                                      = new StockModel;
        $Stock->name                                = $request->name;
        $Stock->stock                               = $request->stock;
        $Stock->cost_of_good                        = set_clearFormatRupiah($request->cost_of_good);
        $Stock->selling_price                       = set_clearFormatRupiah($request->selling_price);
//        $Stock->customer_id                         = $request->customer;
        $Stock->supplier_id                         = $request->supplier;
        $Stock->restock_date                        = DateFormat($request->restock_date,"Y-m-d");
        $Stock->branch_id                           = $Users->branch_id;
        $Stock->is_active                           = 1;
        $Stock->created_by                          = Auth::id();
        $Stock->brand                               = $request->brand;
        $Stock->color                               = $request->color;
        $Stock->name_of_consignment                 = $request->name_of_consignment;
//        $Stock->phone                               = $request->phone;
//        $Stock->rekening                            = $request->rekening;
        $Stock->point                               = $request->point;

        if($Stock->save()){
            $Code                                   = "ITM8".date("ymd").sprintf("%04s",$Stock->id);
            $StockUpdate                            = StockModel::find($Stock->id);
            $StockUpdate->code                      = $Code;
            $StockUpdate->save();

            return redirect()
                ->route('stock_show')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'stock'                     => 'required',
            'cost_of_good'              => 'required',
            'selling_price'             => 'required',
            'supplier'                  => 'required',
            'brand'                     => 'required',
            'name_of_consignment'       => 'required',
            'restock_date'              => 'required',
            'point'                     => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $id                                         = $request->id;

        $Stock                                      = StockModel::find($request->id);
        $Stock->name                                = $request->name;
        $Stock->cost_of_good                        = set_clearFormatRupiah($request->cost_of_good);
        $Stock->selling_price                       = set_clearFormatRupiah($request->selling_price);
        $Stock->is_active                           = 1;
        $Stock->updated_by                          = Auth::id();
        $Stock->brand                               = $request->brand;
        $Stock->color                               = $request->color;
        $Stock->name_of_consignment                 = $request->name_of_consignment;
//        $Stock->phone                               = $request->phone;
//        $Stock->rekening                            = $request->rekening;
        $Stock->point                               = $request->point;

        if($Stock->save()){
            return redirect()
                ->route('stock_show')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function inactive(Request $request){
        $Stock                                      = StockModel::find($request->id);
        $Stock->is_active                           = 0;
        $Stock->updated_by                          = Auth::id();
        if($Stock){
            if($Stock->save()){
                return redirect()
                    ->route('stock_show')
                    ->with('scsMsg',"Data succesfuly deleted");

            }else{
                dd("Error deleted Data Customer");
            }
        }
    }


    public function searchbybranch(Request $request){
        $BranchID                                       = $request->branch_id;
        $Where                                          = array(
            "status"                                    => 1,
            "branch_id"                                 => $BranchID
        );
        $Customer                                       = CustomerModel::where($Where)->get();

            echo '<option value="0">Choose Customer</option>';
        foreach($Customer as $item){
            echo '<option value="'.$item->id.'">' . $item->name . ' [Branch '.$item->branch->name.']</option>';
        }
    }

    public function setbranch(Request $request){
        $CustomerID                                     = $request->customer_id;
        $Customer                                       = CustomerModel::find($CustomerID);
        if($Customer){
            $data                                    = array(
                "status"                                => true,
                "output"                                => $Customer->branch_id
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

    public function info(Request $request){
        $StockID                                        = $request->id;
        $Stock                                          = StockModel::find($StockID);
            if($Stock){
                $data                                    = array(
                    "status"                                => true,
                    "output"                                => array(
                        "code"                              => $Stock->code,
                        "name"                              => $Stock->name,
                        "stock"                             => $Stock->stock,
                        "selling_price"                     => number_format($Stock->selling_price,0,",","."),
                        "cost_of_good"                      => number_format($Stock->cost_of_good,0,",",".")
                    ),
                    "message"                               => "OK"
                );
            }else{
                $data                                    = array(
                    "status"                                => false,
                    "message"                               => 'Stock Not Found'
                );
            }


        return response($data, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function update_stock(Request $request){
        $StockID                                            = $request->id;
        $StockNew                                           = $request->stock;

        if($StockNew == ""){
            $data                                    = array(
                "status"                                => false,
                "output"                                => 0,
                "message"                               => "Masukan Jumlah Stock Baru"
            );
        }else{
            $Stock                                          = StockModel::find($StockID);
            $StockOld                                       = $Stock->stock;
            $NewStock                                       = $StockOld + $StockNew;

            $Stock->stock                                   = $NewStock;
            $Stock->restock_date                            = date("Y-m-d H:i:s");
            if($Stock->save()){
                $data                                    = array(
                    "status"                                => true,
                    "message"                               => "Penambahan stock baru berhasil",
                    "output"                                => array(
                        "Old"                               => $StockOld,
                        "New"                               => $NewStock
                    )
                );

            }else{

                $data                                    = array(
                    "status"                                => false,
                    "message"                               => "Ada kesalahan sistme. Mohon hubungi web developer."
                );

            }
        }

        return response($data, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function generate_barcode($id){
        $Stock                                      = StockModel::find($id);
        $x                                          = $Stock->stock;
        if($x > 36){
            $x = 36;
        }
        for($y=1;$y<=$x;$y++){
            $d                                          = new DNS1D();
            $d->setStorPath(__DIR__."/cache/");
            $Format                                     = "62".substr($Stock->code,4);
            $Format                                     = ean13_check_digit($Format);

            $arrBarcode[$y]['Barcode']                  = $d->getBarcodeHTML($Format, "EAN13");
            $arrBarcode[$y]['Format']                   = $Format;
        }

        $data['ArrBarcode']                             = $arrBarcode;
        $pdf = PDF::loadView('pdf.barcode_generator', $data);
        return $pdf->download($Format.'.pdf');
    }


}
