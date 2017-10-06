<?php

namespace App\Modules\Archive\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Customer\Models\CustomerModel;
use App\Modules\Archive\Models\Archive as ArchiveModel;
use App\Modules\Archive\Models\ArchiveOrder as ArchiveOrderModel;
use App\Modules\Branch\Models\BranchModel;
use Auth;
use Theme;
use App\Modules\User\Models\UserModel;

use App\Modules\Role\Models\Role;
use App\Jobs\SendMail;
use Illuminate\Auth\Events\Registered;
use App\User;

class ArchiveController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:archive-menu']);
        $this->middleware('permission:archive-treatment-view')->only('show');

        $this->_data['string_menuname']             = 'Archive Order';
        $this->_data['IDMENU']                      = 'Archive';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'archive';
        $this->_data['IDSUBMENU']                   = 'ListArchive';

        return Theme::view('modules.archive.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'archive';
        $this->_data['IDSUBMENU']                   = 'ListArchive';

        return Theme::view('modules.archive.show',$this->_data);
    }

    public function datatables(){
        $daysago                        = date('c', strtotime('-60 days'));
        $twoMonth                       = DateFormat($daysago,"Y-m-d");


        $Archive        = ArchiveModel::join('branchs','branchs.id','=','archives.branch_id')
                                    ->join('customers','customers.id', '=','archives.customer_id')
                                    ->select(['archives.id', 'archives.no_transaksi as code', 'archives.transaction_date', 'customers.name as customer', 'archives.status', 'archives.total', 'archives.dp'])
                                    // ->where('archives.subtotal.status','=',1)
                                    ->where('archives.transaction_date','<=',$twoMonth);

        return Datatables::of($Archive)
            ->addColumn('href', function ($Archive) {
                return '<a href="javascript:void(0);" class="btn btn-danger" onclick="DetailList('.$Archive->id.')"><i class="fa fa-list-alt"></i></a>';
            })
            ->editColumn('status', '{{ getStatusArchive($status) }}')
            ->editColumn('total', '{{number_format($total,0,",",".")}}')
            ->editColumn('dp', '{{number_format($dp,0,",",".")}}')
            ->rawColumns(['href','subtotal','diskon','total','dp'])
            ->make(true);
    }

    public function info(Request $request){

        $id                                                 = $request->archive_id;
        $Archive                                            = ArchiveModel::find($id);
        if($Archive){
            $Customer                                       = CustomerModel::find($Archive->customer_id);
            $NoTransaction                                  = $Archive->no_transaksi;
            $Customer                                       = $Customer->name;
            $TransactionDate                                = $Archive->transaction_date;
            $Subtotal                                       = $Archive->subtotal;
            $Additional                                     = $Archive->additional;
            $AdditionalPrice                                = $Archive->additional_price;
            $DP                                             = $Archive->dp;
            $Discount                                       = $Archive->diskon;
            $Total                                          = $Archive->total;
            $PaymentType                                    = $Archive->tipe_pembayaran;
            $Status                                         = $Archive->status;
            $Sisa                                           = $Total - $DP;
            if($Status == 0){
                $Status = "DP";
            }else{
                $Status = "Lunas";
            }

            $data                                    = array(
                "status"                                => true,
                "message"                               => 'success',
                "output"                                => array(
                    "no_transaction"                            => $NoTransaction,
                    "customer"                                  => $Customer,
                    "transaction_date"                          => $TransactionDate,
                    "subtotal"                                  => number_format($Subtotal,0,",","."),
                    "additional"                                => number_format($AdditionalPrice,0,",","."),
                    "dp"                                        => number_format($DP,0,",","."),
                    "discount"                                  => number_format($Discount,0,",","."),
                    "total"                                     => number_format($Total,0,",","."),
                    "payment_type"                              => $PaymentType,
                    "status"                                    => $Status,
                    "sisa"                                      => number_format($Sisa,0,",",".")
                )
            );
        }else{
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Sorry, technical error. Please contact your web developer(15)'
            );
        }
        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }


    public function take_items($archive_id){
        $Archive                                        = ArchiveModel::find($archive_id);
        $Archive->status                                = 1;

        if($Archive->save()){
            return redirect()
                ->route('archive_show')
                ->with('scsMsg',"Transaksi selesai. Barang dengan no transaksi (".$Archive->no_transaksi.") telah diambil dan dilunasi.");
        }

    }
}
