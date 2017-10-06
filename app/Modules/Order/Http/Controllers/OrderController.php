<?php

namespace App\Modules\Order\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Customer\Models\CustomerModel;
use App\Modules\Branch\Models\BranchModel;
use Auth;
use Theme;
use App\Modules\User\Models\UserModel;
use App\Modules\Order\Models\OrderModel;
use App\Modules\Order\Models\OrderDetailModel;
use App\Modules\Order\Models\OrderImageModel;
use App\Modules\Merk\Models\MerkModel;
use App\Modules\Treatmentpackage\Models\TreatmentPackageModel;
use App\Modules\Emailtemplate\Models\EmailTemplate as EmailTemplateModel;

use App\Modules\Role\Models\Role;
use App\Modules\Cashbook\Models\CashBookModel;

use App\Jobs\SendMail;
use Illuminate\Auth\Events\Registered;
use App\User;

use \Milon\Barcode\DNS1D;



class OrderController extends Controller
{
    protected $_data = array();
    protected $destinationPath = array();

    public function __construct()
    {
        $this->middleware(['permission:order-menu-view']);
        $this->middleware('permission:order-add')->only('add');

        $this->destinationPath = public_path('images/item');

        $this->_data['string_menuname']             = 'Order';
        $this->_data['IDMENU']                      = 'Order';
    }

    public function index(){

    }

    public function show(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'List';
        $this->_data['IDSUBMENU']                   = 'ListOrder';

        $Users                                      = Auth::user();

        return Theme::view('modules.order.show',$this->_data);
    }

    public function show_kirimworkshop(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'List';
        $this->_data['IDSUBMENU']                   = 'ListOrder';

        $Users                                      = Auth::user();

        return Theme::view('modules.order.show_kirimworkshop',$this->_data);
    }

    public function show_proccessworkshop(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'List';
        $this->_data['IDSUBMENU']                   = 'ListOrder';

        $Users                                      = Auth::user();

        return Theme::view('modules.order.show_proccessworkshop',$this->_data);
    }

    public function show_kirimcounter(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'List';
        $this->_data['IDSUBMENU']                   = 'ListOrder';

        $Users                                      = Auth::user();

        return Theme::view('modules.order.show_kirimcounter',$this->_data);
    }


    public function show_terimacounter(){
            $this->_data['state']                       = 'add';
            $this->_data['string_active_menu']          = 'List';
            $this->_data['IDSUBMENU']                   = 'ListOrder';

            $Users                                      = Auth::user();

            return Theme::view('modules.order.show_terimacounter',$this->_data);
    }

    public function show_qcchecked(){
            $this->_data['state']                       = 'add';
            $this->_data['string_active_menu']          = 'List';
            $this->_data['IDSUBMENU']                   = 'ListOrder';

            $Users                                      = Auth::user();

            return Theme::view('modules.order.show_qcchecked',$this->_data);
    }


    public function show_all(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'List';
        $this->_data['IDSUBMENU']                   = 'ListOrder';

        $Users                                      = Auth::user();

        return Theme::view('modules.order.show_all',$this->_data);
    }

    public function show_done(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'List';
        $this->_data['IDSUBMENU']                   = 'ListOrder';

        $Users                                      = Auth::user();

        return Theme::view('modules.order.show_done',$this->_data);
    }

    public function show_takeitems(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'List';
        $this->_data['IDSUBMENU']                   = 'ListOrder';

        $Users                                      = Auth::user();

        return Theme::view('modules.order.show_takeitems',$this->_data);
    }


    public function datatables(){
        $Users                          = Auth::user();
        $daysago                        = date('c', strtotime('-60 days'));
        $twoMonth                       = DateFormat($daysago,"Y-m-d");

        $WhereIN                    = array(0,1,2,3,4,5);
        if($Users->can('access-pusat')){
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.invoice','>', 0)
                                ->wherein('orders.status', $WhereIN)
                                ->where('orders.created_by','<=',$twoMonth);
        }else{
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.branch_id','=', $Users->branch_id)
                                ->where('orders.invoice','>', 0)
                                ->wherein('orders.status', $WhereIN)
                                ->where('orders.created_by','<=',$twoMonth);
        }



        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="javascript:void(0);" class="btn btn-info" onclick="viewOrder('.$Order->id.')"><i class="glyphicon glyphicon-search"></i></a>';
            })

            ->editColumn('paid', function ($Order) {
                if($Order->paid == 1){
                    return '<span class="label label-sm label-success">'.get_lunas($Order->paid).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_lunas($Order->paid).'</span>';
                }
            })

            ->editColumn('date_transaction', function ($Order) {
                    return DateFormat($Order->date_transaction,"d/m/Y H:i:s");
            })


            ->editColumn('status', function ($Order) {
                if($Order->status == 2){
                    return '<span class="label label-sm label-success">'.get_statusorder($Order->status).'</span>';
                }else if($Order->status == 0){
                    return '<span class="label label-sm label-danger">'.get_statusorder($Order->status).'</span>';
                }else{
                    return '<span class="label label-sm label-warning">'.get_statusorder($Order->status).'</span>';
                }
            })

            ->editColumn('pay', function ($Order) {
                return number_format($Order->down_payment + $Order->pay,0,",",".");
            })

            ->editColumn('payment', '{{ number_format($payment,0,",",".") }}')
            ->editColumn('created_by', '{{ get_NameUser($created_by) }}')
            ->editColumn('updated_by', '{{ get_NameUser($updated_by) }}')
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }


    public function datatables_kirimworkshop(){
        $Users                          = Auth::user();
        $daysago                        = date('c', strtotime('-60 days'));
        $twoMonth                       = DateFormat($daysago,"Y-m-d");

        if($Users->can('access-pusat')){
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 1)
                                ->where('orders.created_by','<=',$twoMonth);
        }else{
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.branch_id','=', $Users->branch_id)
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 1)
                                ->where('orders.created_by','<=',$twoMonth);
        }

        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="javascript:void(0);" class="btn btn-info" onclick="viewOrder('.$Order->id.')"><i class="glyphicon glyphicon-search"></i></a>';
            })

            ->editColumn('paid', function ($Order) {
                if($Order->paid == 1){
                    return '<span class="label label-sm label-success">'.get_lunas($Order->paid).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_lunas($Order->paid).'</span>';
                }
            })

            ->editColumn('date_transaction', function ($Order) {
                    return DateFormat($Order->date_transaction,"d/m/Y H:i:s");
            })


            ->editColumn('status', function ($Order) {
                if($Order->status == 2){
                    return '<span class="label label-sm label-success">'.get_statusorder($Order->status).'</span>';
                }else if($Order->status == 0){
                    return '<span class="label label-sm label-danger">'.get_statusorder($Order->status).'</span>';
                }else{
                    return '<span class="label label-sm label-warning">'.get_statusorder($Order->status).'</span>';
                }
            })

            ->editColumn('pay', function ($Order) {
                return number_format($Order->down_payment + $Order->pay,0,",",".");
            })

            ->editColumn('payment', '{{ number_format($payment,0,",",".") }}')
            ->editColumn('created_by', '{{ get_NameUser($created_by) }}')
            ->editColumn('updated_by', '{{ get_NameUser($updated_by) }}')
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }

    public function datatables_proccessworkshop(){
        $Users                          = Auth::user();
        $daysago                        = date('c', strtotime('-60 days'));
        $twoMonth                       = DateFormat($daysago,"Y-m-d");

        if($Users->can('access-pusat')){
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 2)
                                ->where('orders.created_by','<=',$twoMonth);
        }else{
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.branch_id','=', $Users->branch_id)
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 2)
                                ->where('orders.created_by','<=',$twoMonth);
        }

        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="javascript:void(0);" class="btn btn-info" onclick="viewOrder('.$Order->id.')"><i class="glyphicon glyphicon-search"></i></a>';
            })

            ->editColumn('paid', function ($Order) {
                if($Order->paid == 1){
                    return '<span class="label label-sm label-success">'.get_lunas($Order->paid).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_lunas($Order->paid).'</span>';
                }
            })

            ->editColumn('date_transaction', function ($Order) {
                    return DateFormat($Order->date_transaction,"d/m/Y H:i:s");
            })


            ->editColumn('status', function ($Order) {
                if($Order->status == 2){
                    return '<span class="label label-sm label-success">'.get_statusorder($Order->status).'</span>';
                }else if($Order->status == 0){
                    return '<span class="label label-sm label-danger">'.get_statusorder($Order->status).'</span>';
                }else{
                    return '<span class="label label-sm label-warning">'.get_statusorder($Order->status).'</span>';
                }
            })

            ->editColumn('pay', function ($Order) {
                return number_format($Order->down_payment + $Order->pay,0,",",".");
            })

            ->editColumn('payment', '{{ number_format($payment,0,",",".") }}')
            ->editColumn('created_by', '{{ get_NameUser($created_by) }}')
            ->editColumn('updated_by', '{{ get_NameUser($updated_by) }}')
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }

    public function datatables_kirimcounter(){
        $Users                          = Auth::user();
        $daysago                        = date('c', strtotime('-60 days'));
        $twoMonth                       = DateFormat($daysago,"Y-m-d");

        if($Users->can('access-pusat')){
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 3)
                                ->where('orders.created_by','<=',$twoMonth);
        }else{
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.branch_id','=', $Users->branch_id)
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 3)
                                ->where('orders.created_by','<=',$twoMonth);
        }

        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="javascript:void(0);" class="btn btn-info" onclick="viewOrder('.$Order->id.')"><i class="glyphicon glyphicon-search"></i></a>';
            })

            ->editColumn('paid', function ($Order) {
                if($Order->paid == 1){
                    return '<span class="label label-sm label-success">'.get_lunas($Order->paid).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_lunas($Order->paid).'</span>';
                }
            })

            ->editColumn('date_transaction', function ($Order) {
                    return DateFormat($Order->date_transaction,"d/m/Y H:i:s");
            })


            ->editColumn('status', function ($Order) {
                if($Order->status == 2){
                    return '<span class="label label-sm label-success">'.get_statusorder($Order->status).'</span>';
                }else if($Order->status == 0){
                    return '<span class="label label-sm label-danger">'.get_statusorder($Order->status).'</span>';
                }else{
                    return '<span class="label label-sm label-warning">'.get_statusorder($Order->status).'</span>';
                }
            })

            ->editColumn('pay', function ($Order) {
                return number_format($Order->down_payment + $Order->pay,0,",",".");
            })

            ->editColumn('payment', '{{ number_format($payment,0,",",".") }}')
            ->editColumn('created_by', '{{ get_NameUser($created_by) }}')
            ->editColumn('updated_by', '{{ get_NameUser($updated_by) }}')
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }


    public function datatables_terimacounter(){
        $Users                          = Auth::user();
        $daysago                        = date('c', strtotime('-60 days'));
        $twoMonth                       = DateFormat($daysago,"Y-m-d");

        if($Users->can('access-pusat')){
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 4)
                                ->where('orders.created_by','<=',$twoMonth);
        }else{
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.branch_id','=', $Users->branch_id)
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 4)
                                ->where('orders.created_by','<=',$twoMonth);
        }

        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="javascript:void(0);" class="btn btn-info" onclick="viewOrder('.$Order->id.')"><i class="glyphicon glyphicon-search"></i></a>';
            })

            ->editColumn('paid', function ($Order) {
                if($Order->paid == 1){
                    return '<span class="label label-sm label-success">'.get_lunas($Order->paid).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_lunas($Order->paid).'</span>';
                }
            })

            ->editColumn('date_transaction', function ($Order) {
                    return DateFormat($Order->date_transaction,"d/m/Y H:i:s");
            })


            ->editColumn('status', function ($Order) {
                if($Order->status == 2){
                    return '<span class="label label-sm label-success">'.get_statusorder($Order->status).'</span>';
                }else if($Order->status == 0){
                    return '<span class="label label-sm label-danger">'.get_statusorder($Order->status).'</span>';
                }else{
                    return '<span class="label label-sm label-warning">'.get_statusorder($Order->status).'</span>';
                }
            })

            ->editColumn('pay', function ($Order) {
                return number_format($Order->down_payment + $Order->pay,0,",",".");
            })

            ->editColumn('payment', '{{ number_format($payment,0,",",".") }}')
            ->editColumn('created_by', '{{ get_NameUser($created_by) }}')
            ->editColumn('updated_by', '{{ get_NameUser($updated_by) }}')
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }

    public function datatables_qcchecked(){
        $Users                          = Auth::user();
        $daysago                        = date('c', strtotime('-60 days'));
        $twoMonth                       = DateFormat($daysago,"Y-m-d");

        if($Users->can('access-pusat')){
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 5)
                                ->where('orders.created_by','<=',$twoMonth);
        }else{
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.branch_id','=', $Users->branch_id)
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 5)
                                ->where('orders.created_by','<=',$twoMonth);
        }

        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="javascript:void(0);" class="btn btn-info" onclick="viewOrder('.$Order->id.')"><i class="glyphicon glyphicon-search"></i></a>';
            })

            ->editColumn('paid', function ($Order) {
                if($Order->paid == 1){
                    return '<span class="label label-sm label-success">'.get_lunas($Order->paid).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_lunas($Order->paid).'</span>';
                }
            })

            ->editColumn('date_transaction', function ($Order) {
                    return DateFormat($Order->date_transaction,"d/m/Y H:i:s");
            })


            ->editColumn('status', function ($Order) {
                if($Order->status == 2){
                    return '<span class="label label-sm label-success">'.get_statusorder($Order->status).'</span>';
                }else if($Order->status == 0){
                    return '<span class="label label-sm label-danger">'.get_statusorder($Order->status).'</span>';
                }else{
                    return '<span class="label label-sm label-warning">'.get_statusorder($Order->status).'</span>';
                }
            })

            ->editColumn('pay', function ($Order) {
                return number_format($Order->down_payment + $Order->pay,0,",",".");
            })

            ->editColumn('payment', '{{ number_format($payment,0,",",".") }}')
            ->editColumn('created_by', '{{ get_NameUser($created_by) }}')
            ->editColumn('updated_by', '{{ get_NameUser($updated_by) }}')
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }




    public function datatables_all(){
        $Users                          = Auth::user();
        $daysago                        = date('c', strtotime('-60 days'));
        $twoMonth                       = DateFormat($daysago,"Y-m-d");

        if($Users->can('access-pusat')){
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.invoice','>', 0)
                                ->where('orders.created_by','<=',$twoMonth);
        }else{
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.branch_id','=', $Users->branch_id)
                                ->where('orders.invoice','>', 0)
                                ->where('orders.created_by','<=',$twoMonth);
        }



        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="javascript:void(0);" class="btn btn-info" onclick="viewOrder('.$Order->id.')"><i class="glyphicon glyphicon-search"></i></a>';
            })

            ->editColumn('paid', function ($Order) {
                if($Order->paid == 1){
                    return '<span class="label label-sm label-success">'.get_lunas($Order->paid).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_lunas($Order->paid).'</span>';
                }
            })

            ->editColumn('date_transaction', function ($Order) {
                    return DateFormat($Order->date_transaction,"d/m/Y H:i:s");
            })


            ->editColumn('status', function ($Order) {
                if($Order->status == 2){
                    return '<span class="label label-sm label-success">'.get_statusorder($Order->status).'</span>';
                }else if($Order->status == 0){
                    return '<span class="label label-sm label-danger">'.get_statusorder($Order->status).'</span>';
                }else{
                    return '<span class="label label-sm label-warning">'.get_statusorder($Order->status).'</span>';
                }
            })

            ->editColumn('pay', function ($Order) {
                return number_format($Order->down_payment + $Order->pay,0,",",".");
            })

            ->editColumn('payment', '{{ number_format($payment,0,",",".") }}')
            ->editColumn('created_by', '{{ get_NameUser($created_by) }}')
            ->editColumn('updated_by', '{{ get_NameUser($updated_by) }}')
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }

    public function datatables_done(){
        $Users                          = Auth::user();
        $daysago                        = date('c', strtotime('-60 days'));
        $twoMonth                       = DateFormat($daysago,"Y-m-d");

        if($Users->can('access-pusat')){
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 6);
        }else{
            $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                ->where('orders.branch_id','=', $Users->branch_id)
                                ->where('orders.invoice','>', 0)
                                ->where('orders.status', 6);
        }
        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="javascript:void(0);" class="btn btn-info" onclick="viewOrder('.$Order->id.')"><i class="glyphicon glyphicon-search"></i></a>';
            })

            ->editColumn('paid', function ($Order) {
                if($Order->paid == 1){
                    return '<span class="label label-sm label-success">'.get_lunas($Order->paid).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_lunas($Order->paid).'</span>';
                }
            })

            ->editColumn('date_transaction', function ($Order) {
                    return DateFormat($Order->date_transaction,"d/m/Y H:i:s");
            })


            ->editColumn('status', function ($Order) {
                if($Order->status == 2){
                    return '<span class="label label-sm label-success">'.get_statusorder($Order->status).'</span>';
                }else if($Order->status == 0){
                    return '<span class="label label-sm label-danger">'.get_statusorder($Order->status).'</span>';
                }else{
                    return '<span class="label label-sm label-warning">'.get_statusorder($Order->status).'</span>';
                }
            })


            ->editColumn('pay', function ($Order) {
                return number_format($Order->down_payment + $Order->pay,0,",",".");
            })

            ->editColumn('payment', '{{ number_format($payment,0,",",".") }}')
            ->editColumn('created_by', '{{ get_NameUser($created_by) }}')
            ->editColumn('updated_by', '{{ get_NameUser($updated_by) }}')
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }


        public function datatables_takeitems(){
            $Users                          = Auth::user();
            $daysago                        = date('c', strtotime('-60 days'));
            $twoMonth                       = DateFormat($daysago,"Y-m-d");

            if($Users->can('access-pusat')){
                $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                    ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                    ->where('orders.invoice','>', 0)
                                    ->where('orders.status', 7)
                                    ->where('orders.created_by','<=',$twoMonth);
            }else{
                $Order = OrderModel::join('branchs','branchs.id','=','orders.branch_id')
                                    ->select(['orders.id', 'branchs.name as branch','orders.ref_number as code','orders.date_transaction','orders.customer_name as customer', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status','orders.created_by','orders.updated_by'])
                                    ->where('orders.branch_id','=', $Users->branch_id)
                                    ->where('orders.invoice','>', 0)
                                    ->where('orders.status', 7)
                                    ->where('orders.created_by','<=',$twoMonth);
            }



        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="javascript:void(0);" class="btn btn-info" onclick="viewOrder('.$Order->id.')"><i class="glyphicon glyphicon-search"></i></a>';
            })

            ->editColumn('paid', function ($Order) {
                if($Order->paid == 1){
                    return '<span class="label label-sm label-success">'.get_lunas($Order->paid).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_lunas($Order->paid).'</span>';
                }
            })

            ->editColumn('date_transaction', function ($Order) {
                    return DateFormat($Order->date_transaction,"d/m/Y H:i:s");
            })


            ->editColumn('status', function ($Order) {
                if($Order->status == 2){
                    return '<span class="label label-sm label-success">'.get_statusorder($Order->status).'</span>';
                }else if($Order->status == 0){
                    return '<span class="label label-sm label-danger">'.get_statusorder($Order->status).'</span>';
                }else{
                    return '<span class="label label-sm label-warning">'.get_statusorder($Order->status).'</span>';
                }
            })


            ->editColumn('pay', function ($Order) {
                return number_format($Order->down_payment + $Order->pay,0,",",".");
            })

            ->editColumn('payment', '{{ number_format($payment,0,",",".") }}')
            ->editColumn('created_by', '{{ get_NameUser($created_by) }}')
            ->editColumn('updated_by', '{{ get_NameUser($updated_by) }}')
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }


    public function step1($CustomerID){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddOrder';

        $Customer                                   = CustomerModel::find($CustomerID);

        $Users                                      = Auth::user();
        $Order                                      = new OrderModel;
        $Order->date_transaction                    = date('Y-m-d H:i:s');
        $Order->branch_id                           = $Users->branch_id;
        if($Customer){
            $Order->customer_id                         = $CustomerID;
            $Order->customer_name                       = $Customer->name;
        }
        $Order->paid                                = 0;
        $Order->invoice                             = 0;
        $Order->total                               = 0;
        $Order->discount                            = 0;
        $Order->additional                          = 0;
        $Order->created_by                          = Auth::id();


        if($Order->save()){
            $OrderUpdate                            = OrderModel::find($Order->id);
            $OrderUpdate->ref_number                = "YBS".date("ymd").sprintf("%04s",$Order->id);
            $OrderUpdate->save();
            return redirect()
                ->route('order_show_step2',$Order->id)
                ->with('infoMsg',"Complete this form");
        }

        // return Theme::view('modules.order.step1',$this->_data);
    }


    public function save_step1(Request $request){
        $validator = Validator::make($request->all(), [
            'date_transaction'      => 'required',
            'customer'              => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $Customer                                   = CustomerModel::find($request->customer);
        $Users                                      = Auth::user();
        $Order                                      = new OrderModel;
        $Order->date_transaction                    = DateFormat($request->date_transaction,"Y-m-d");
        $Order->branch_id                           = $Users->branch_id;
        $Order->customer_id                         = $request->customer;
        $Order->customer_name                       = $Customer->name;
        $Order->paid                                = 0;
        $Order->invoice                             = 0;
        $Order->total                               = 0;
        $Order->discount                            = 0;
        $Order->additional                          = 0;
        $Order->created_by                          = Auth::id();

        if($Order->save()){
            $OrderUpdate                            = OrderModel::find($Order->id);
            $OrderUpdate->ref_number                = "YBS".date("ymd").sprintf("%04s",$Order->id);
            $OrderUpdate->save();
            return redirect()
                ->route('order_show_step2',$Order->id)
                ->with('infoMsg',"Complete this form");
        }
    }

    public function step2($id){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddOrder';


        $Users                                      = Auth::user();
        $Order                                      = OrderModel::find($id);
        $CustomerID                                 = $Order->customer_id;
        $this->_data['BranchID']                    = $Users->branch_id;
        $this->_data['BranchAddedID']               = $Order->branch_id;

        $this->_data['DateNow']                     = date('d-m-Y');
        if($CustomerID > 0){
            $Customer                               = CustomerModel::find($CustomerID);
            $this->_data['BranchID']                = $Customer->branch_id;
            $this->_data['CustomerID']              = $CustomerID;
            $this->_data['CustomerName']            = $Customer->name;
        }

        $this->_data['order_id']                    = $id;

        $this->_data['Order']                       = $Order;

        $d                                          = new DNS1D();
        $d->setStorPath(__DIR__."/cache/");
        $Format                                         = "62".substr($Order->ref_number,3);
        $Format                                         = ean13_check_digit($Format);

        $this->_data['Barcode']                     = $d->getBarcodeHTML($Format, "EAN13");

        $OrderDetail                                = OrderDetailModel::where('order_id','=',$id)->get();
        $this->_data['OrderDetail']                 = $OrderDetail;
        // return Theme::view('modules.order.step1',$this->_data);
        return Theme::view('modules.order.step1',$this->_data);
    }

    public function save_step2(Request $request){
        $validator = Validator::make($request->all(), [
            'id'                    => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $id_order                                  = $request->id;

        if(OrderDetailModel::where('order_id',$id_order)->count() == 0){
            return redirect()->back()->withInput($request->input())->with('errMsg',"Please add item Order");
        }

        $OrderSum                               = OrderDetailModel::where('order_id',$id_order)->sum('total');
        $Order                                  = OrderModel::find($id_order);
        $Order->total                           = $OrderSum;
        if($Order->save()){
            return redirect()
                ->route('order_show_step3',$Order->id)
                ->with('infoMsg',"Complete this form");
        }else{
            return redirect()->back()->withInput($request->input())->with('errMsg',"Sorry, technical error. Please contact your web developer.(2)");
        }

    }

    public function step3($id){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddOrder';
        $Users                                      = Auth::user();
        $Order                                      = OrderModel::find($id);
        $this->_data['order_id']                    = $id;

        $this->_data['Order']                       = $Order;

        $OrderDetail                                = OrderDetailModel::where('order_id','=',$id)->get();
        $this->_data['OrderDetail']                 = $OrderDetail;

        return Theme::view('modules.order.step3',$this->_data);
    }

    public function save_step3(Request $request){
        $validator = Validator::make($request->all(), [
            'id'                    => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $id_order                                  = $request->id;

        if(OrderDetailModel::where('order_id',$id_order)->count() == 0){
            return redirect()->back()->withInput($request->input())->with('errMsg',"Please add item Order");
        }

        return redirect()
            ->route('order_show_step4',$id_order)
            ->with('infoMsg',"Please Confirmation Order");

    }

    public function step4($id){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddOrder';

        $Users                                      = Auth::user();
        $Order                                      = OrderModel::find($id);
        $this->_data['order_id']                    = $id;

        $this->_data['Order']                       = $Order;

        $OrderDetail                                = OrderDetailModel::where('order_id','=',$id)->get();
        $this->_data['OrderDetail']                 = $OrderDetail;

        $OrderImage                                 = OrderImageModel::where('order_id','=',$id)->get();
        $this->_data['OrderImage']                  = $OrderImage;


        return Theme::view('modules.order.step4',$this->_data);
    }

    public function save_laststep(Request $request){
        $validator = Validator::make($request->all(), [
            'id'                        => 'required',
            'customer'                  => 'required',
            'total_header'              => 'required',
            'grandtotal'                => 'required',
            'type'                      => 'required',
            'payment'                   => 'required',
            'payment_left'              => 'required',
            'payment_type'              => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $Users                                          = User::find(Auth::id());

        $id_order                                       = $request->id;
        $OrderInfo                                      = OrderModel::find($id_order);
        $CustomerID                                     = $request->customer;
        $GrandTotal                                     = set_clearFormatRupiah($request->grandtotal);
        $DiscountHeader                                 = set_clearFormatRupiah($request->discount_header);
        $AdditionalHeader                               = set_clearFormatRupiah($request->additional_header);
        $Payment                                        = set_clearFormatRupiah($request->payment);
        $PaymentLeft                                    = set_clearFormatRupiah($request->payment_left);
        $BranchIDOrder                                  = $Users->branch_id;

        // dd($request);
        if($request->type == 1){ ## FULL PAYMENT ##
            if($Payment < $GrandTotal){
                return redirect()
                        ->back()
                        ->with('errMsg','Full Payment Type is required to fill the full nominal.')
                        ->withInput($request->input());
            }
        }

        if($DiscountHeader == ""){
            $Discount                                   = 0;
        }else{
            $Discount                                   = $DiscountHeader;
        }

        if($AdditionalHeader == ""){
            $Additional                                 = 0;
        }else{
            $Additional                                 = $AdditionalHeader;
        }

        $Order                                          = OrderModel::find($id_order);
        $Order->date_transaction                        = DateFormat($request->date_transaction,"Y-m-d");
        $Order->discount                                = set_clearFormatRupiah($Discount);
        $Order->additional                              = set_clearFormatRupiah($Additional);
        # dp = 0, full = 1 ##
        $Order->type                                    = $request->type;
        if($Users->can('access-pusat')){
            $Order->branch_id                           = $request->branch_added;
            $BranchIDOrder                              = $request->branch_added;
        }

        if($request->type == 0){ # DP #
            $Order->down_payment                        = set_clearFormatRupiah($Payment);
            $Order->full_payment                        = 0;
            $Order->invoice                             = 1;
            # 0 = belum lunas, 1 = lunas ##
            $Order->payment_type_id                     = $request->payment_type;
            if($Payment >= $GrandTotal){
                $Order->paid                            = 1; # LUNAS #
                $Order->full_payment                    = set_clearFormatRupiah($Payment);
            }
        }else if($request->type == 1){ ## FULL PAYMENT ##
            $Order->down_payment                        = 0;
            $Order->full_payment                        = set_clearFormatRupiah($Payment);
            $Order->invoice                             = 2;
            # 0 = belum lunas, 1 = lunas ##
            $Order->payment_type_full_id                = $request->payment_type;
            if($Payment >= $GrandTotal){
                # 0 = belum lunas, 1 = lunas ##
                $Order->payment_type_full_id            = $request->payment_type;
                $Order->paid                            = 1; # LUNAS #
                $Order->full_payment                    = set_clearFormatRupiah($Payment);
            }
        }

        $GrandTotal                                     = set_clearFormatRupiah($GrandTotal);
        $Discount                                       = set_clearFormatRupiah($Discount);
        $Additional                                     = set_clearFormatRupiah($Additional);
        $Payment                                        = set_clearFormatRupiah($request->payment);
        $NominalDiscount                                = $GrandTotal * $Discount / 100;


        // $PaymentLeft                                    = $GrandTotal - $NominalDiscount + $Additional - $Payment;
        $Order->payment_left                            = $PaymentLeft;


        $Order->customer_id                             = $CustomerID;
        $Order->customer_name                           = $Order->customer->name;

        $Order->event_id                                = $request->event;

        # 0 = proses, 1 = selesai
        $Order->status                                  = 0;
        if($Order->save()){

            ### CASHBOOK ###
            $CASHBOOK                                       = new CashBookModel;
            $CASHBOOK->debit                                = $Payment;
            $CASHBOOK->credit                               = 0;
            $CASHBOOK->ref_id                               = $Order->id;
            $CASHBOOK->flow                                 = "I"; // IN
            $CASHBOOK->status                               = 1; //INCOME
            $CASHBOOK->date_transaction                     = date('Y-m-d H:i:s');
            $CASHBOOK->branch_id                            = $BranchIDOrder;
            $CASHBOOK->customer_id                          = $CustomerID;
            $CASHBOOK->flow                                 = 0; // ACTIVE //
            $CASHBOOK->created_by                           = Auth::id();
            $CASHBOOK->url                                  = '/order/details/';
            $CASHBOOK->event_id                             = $request->event;


            if($CASHBOOK->save()){
                if($request->payment_type_full_id == 1){
                    $CodeAccount                        = "KM";
                }else{
                    $CodeAccount                        = "BM";
                }

                $Notransaction                          = $CodeAccount.date("ymd").sprintf("%05s",$CASHBOOK->id);
                $CashBookUpdate                         = CashBookModel::find($CASHBOOK->id);
                $CashBookUpdate->notransaction          = $Notransaction;
                $CashBookUpdate->description            = 'Transaksi dari '.$Notransaction.' oleh '.$Order->customer->name.' dengan pembayaran sebesar Rp '.number_format(set_clearFormatRupiah($Payment),0,",",".").',-';
                if($CashBookUpdate->save()){
                    set_SaldoBranch($BranchIDOrder,$Payment,'IN');
                }
            }
            ### CASHBOOK ###


            ### SEND MAIL MODE ###
            $id                                             = $Order->id;
            $Order                                          = OrderModel::find($id);
            $OrderDetail                                    = OrderDetailModel::where("order_id",$id)->get();
            $OrderImage                                     = OrderImageModel::where("order_id",$id)->get();
            $Subtotal                                       = array();
            foreach ($OrderDetail as $item) {
                if($item->treatmentpackage->point > 0){
                    set_TransactionPoint($CustomerID,$item->treatmentpackage->point,'IN');
                }
                $Pricex                                     = $item->price;
                $Discountx                                  = $item->discount;
                $Additionalx                                = $item->additional;
                $DiscountNominalx                           = $Pricex * $Discountx / 100;
                $Subtotals                                  = $Pricex - $DiscountNominalx + $Additionalx;
                array_push($Subtotal, $Subtotals);
            }
            $TotalOrderDetail                               = array_sum($Subtotal);
            $SumAdditional                                  = OrderDetailModel::where("order_id",$id)->sum('additional');


            $Attachment                                     = array();
            foreach($OrderImage as $image){
                array_push($Attachment,'images/item/'.$image->file);
            }

            $EmailParams                            = array(
                'Subject'                               => $Users->name." dari Your Bag Spa",
                'Views'                                 => "email.invoice",
                'Users'                                 => $Users,
                'To'                                    => $Order->customer->email,
                'DateNow'                               => date('d F Y'),
                'Order'                                 => $Order,
                'Discount'                              => $Order->total * $Order->discount / 100,
                'TotalOrderDetail'                      => $TotalOrderDetail,
                'OrderDetail'                           => $OrderDetail,
                'OrderImage'                            => $OrderImage,
                'attachment'                            => $Attachment
            );

            if($Order->customer->email){
                //  dispatch(new SendMail($EmailParams));
            }
            ### SEND MAIL MODE ###

            $data                                       = array(
                "scsMsg"                                => 'Invoice <strong>['.$Order->ref_number.']</strong> Succesfuly Create',
                "invoice"                               => route('order_invoice',$id_order)
            );
            return redirect()
                ->route('order_invoice',$Order->id)
                ->with($data);
        }


    }

    public function calculate(Request $request){
        $Price                                      = set_clearFormatRupiah($request->price);
        $Discount                                   = set_clearFormatRupiah($request->discount);
        $Additional                                 = set_clearFormatRupiah($request->additional);
        $NominalDiscount                            = $Price * $Discount / 100;
        $data                                       = array(
                "total"                             => number_format($Price - $NominalDiscount + $Additional,0,",","."),
                "nominaldiscount"                   => number_format($NominalDiscount,0,",","."),
        );

        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function calculate_header(Request $request){
        $Total                                      = set_clearFormatRupiah($request->total);
        $Discount                                   = set_clearFormatRupiah($request->discount);
        $Additional                                 = set_clearFormatRupiah($request->additional);
        $NominalDiscount                            = $Total * $Discount / 100;
        $data                                       = array(
                "total"                             => number_format($Total - $NominalDiscount + $Additional,0,",","."),
                "nominaldiscount"                   => number_format($NominalDiscount,0,",","."),
        );

        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function calculate_result(Request $request){
        $Total                                      = set_clearFormatRupiah($request->total);
        $Payment                                    = set_clearFormatRupiah($request->payment);
        $data                                       = number_format($Total - $Payment,0,",",".");

        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function calculate_cost(Request $request){
        $Price                                      = set_clearFormatRupiah($request->price);
        $Discount                                   = set_clearFormatRupiah($request->discount);
        $NominalDiscount                            = $Price * $Discount / 100;
        $Additional                                 = set_clearFormatRupiah($request->additional);
        $ShippingCost                               = set_clearFormatRupiah($request->shipping_costs);
        $DownPayment                                = set_clearFormatRupiah($request->down_payment);

        $data                                    = array(
            "status"                                    => true,
            "total"                                     => number_format($Price - $NominalDiscount + $Additional + $ShippingCost,0,",","."),
            "sisa"                                      => number_format($Price - $NominalDiscount + $Additional - $DownPayment + $ShippingCost,0,",",".")
        );

        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }



    public function save_item(Request $request){
        $Merk                                       = $request->merk;
        $Treatment                                  = $request->treatment;
        $Category                                   = $request->category;
        $Package                                    = $request->package;
        $Price                                      = $request->price;
        $Discount                                   = $request->discount;
        $Additional                                 = $request->additional;
        $AdditionalDescription                      = $request->additional_description;
        $Total                                      = $request->total;
        $OrderID                                    = $request->order_id;

        if($Merk == 0){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Merk is Required'
            );
        }else if($Treatment == 0){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Treatment is Required'
            );
        }else if($Package == 0){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Package is Required'
            );
        }else{
            $MerkInfo                               = MerkModel::find($Merk);
            $MerkName                               = $MerkInfo->name;

            $PackageInfo                            = TreatmentPackageModel::find($Package);
            $PackageName                            = $PackageInfo->name;

            $OrderDetail                            = new OrderDetailModel();
            $OrderDetail->treatment_id              = $Treatment;
            $OrderDetail->merk_id                   = $Merk;
            $OrderDetail->treatment_category_id     = $Category;
            $OrderDetail->treatment_package_id      = $Package;
            $OrderDetail->treatment_package         = $PackageName;
            $OrderDetail->merk_name                 = $MerkName;
            $OrderDetail->price                     = set_clearFormatRupiah($Price);
            $OrderDetail->discount                  = set_clearFormatRupiah($Discount);
            $OrderDetail->additional                = set_clearFormatRupiah($Additional);
            $OrderDetail->total                     = set_clearFormatRupiah($Total);
            $OrderDetail->order_id                  = $OrderID;
            $OrderDetail->additional_description    = $AdditionalDescription;
            $OrderDetail->created_by                = Auth::id();
            $OrderDetail->save();
            if($Category != "" || $Category > 0){
                $CategoryInfo                       = $OrderDetail->treatmentcategory->name;
            }else{
                $CategoryInfo                       = "-";
            }
            $DisplayPrice                           = set_clearFormatRupiah($Price);
            $DisplayDiscount                        = set_clearFormatRupiah($Discount);
            $DisplayAdditional                      = set_clearFormatRupiah($Additional);
            $DisplayTotal                           = set_clearFormatRupiah($Total);

            $OrderInfo                              = OrderModel::find($OrderID);
            $HeaderTotal                            = $OrderInfo->total;
            $Total                                  = $HeaderTotal + $DisplayTotal;
            $OrderInfo->total                       = $Total;
            $OrderInfo->save();
            $data                                    = array(
                "status"                                => true,
                "message"                               => 'Item succesful add',
                "output"                                => array(
                    "treatment"                         => $OrderDetail->treatment->name,
                    "merk"                              => $MerkName,
                    "category"                          => $CategoryInfo,
                    "package"                           => $OrderDetail->treatmentpackage->name,
                    "nominaldiscount"                   => number_format($DisplayPrice * $DisplayDiscount / 100,0,",","."),
                    "discount"                          => $DisplayDiscount,
                    "additional"                        => number_format($DisplayAdditional,0,",","."),
                    "additional_description"            => $AdditionalDescription,
                    "image"                             => get_orderimage($OrderDetail->id),
                    "total"                             => number_format($DisplayTotal,0,",","."),
                    "total_header"                      => number_format($Total,0,",","."),
                    "id"                                => $OrderDetail->id
                )
            );
        }

        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function deletedetail(Request $request){
        $id                                         = $request->id;
        $OrderDetail                                = OrderDetailModel::find($id);
        $OrderID                                    = $OrderDetail->order_id;
        $Discount                                   = $OrderDetail->price * $OrderDetail->discount / 100;
        $Price                                      = $OrderDetail->price;
        $Additional                                 = $OrderDetail->additional;
        $NominalDelete                              = $Price - $Discount + $Additional;

        if($OrderDetail->delete()){
            $Order                                  = OrderModel::find($OrderID);
            $Total                                  = $Order->total;
            $TotalUpdate                            = $Total - $NominalDelete;
            $Order->total                           = $Total - $NominalDelete;
            $Order->save();
            $data                                    = array(
                "status"                                => true,
                "message"                               => 'Data successful Delete',
                "output"                                => array(
                    'total_header'                      => number_format($TotalUpdate,0,",",".")
                )
            );
        }else{
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Sory, technical error. Please contact your web developer(1)'
            );
        }

        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function getdetail(Request $request){
        $id                                         = $request->id;
        $OrderDetail                                = OrderDetailModel::find($id);
        if($OrderDetail){
            if($OrderDetail->treatment_category_id){
                $Category                           = $OrderDetail->treatmentcategory->name;
            }else{
                $Category                           = "-";
            }
            $data                                    = array(
                "status"                                => true,
                "message"                               => 'succss',
                "output"                                => array(
                    "treatment"                         => $OrderDetail->treatment->name,
                    "merk"                              => $OrderDetail->merk->name,
                    "category"                          => $Category,
                    "package"                           => $OrderDetail->treatmentpackage->name
                )
            );
        }else{
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Sorry, technical error. Please contact your web developer(3)'
            );
        }
        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function upload(Request $request){
        $File                                       = $request->file('imageFile');
        $OrderID                                    = $request->order_id;
        $OrderDetailID                              = $request->order_detail_id;
        $Count                                      = count($File);
        $Images                                     = "";
        for($i=0; $i<$Count;$i++){
            $ImageFiles                             = $this->imageUpload($File[$i]);
            if($ImageFiles){
                $OrderImage                         = new OrderImageModel();
                $OrderImage->file                   = $ImageFiles;
                $OrderImage->order_id               = $OrderID;
                $OrderImage->order_detail_id        = $OrderDetailID;
                $OrderImage->created_by             = Auth::id();
                $OrderImage->save();
                $Images                            .= '<img src="'.url("/").'/images/item/'.$ImageFiles.'" style="width:50px;height:50px;">';
            }else{
                $data                                    = array(
                    "status"                                => false,
                    "message"                               => 'Sorry, technical error. Please contact your web developer(4)'
                );
                return response($data, 200)
                ->header('Content-Type', 'text/plain');
            }
        }
        $data                                    = array(
            "status"                                => true,
            "order_detail_id"                       => $OrderDetailID,
            "images"                                => $Images,
            "message"                               => 'Success'
        );
        return response($data, 200)
        ->header('Content-Type', 'text/plain');

    }

    /**
     * Processing Save Image File.
     * @param \Illuminate\Http\UploadedFile Get Uploaded file for save it on server folder
     * @return String saved FileName
     */
    public function imageUpload($file_image){
        if ($file_image->isValid()) {
            $str_fileName = time().'-'.$file_image->getClientOriginalName();
            $file_image->move($this->destinationPath, $str_fileName);

            return $str_fileName;
        }
    }

    public function get_detailorder(Request $request){
        $id                                         = $request->order_id;
        $Order                                      = OrderModel::find($id);
        if($Order){
            $OrderDetail                                    = OrderDetailModel::where("order_id",$id)->get();

            $Subtotal                                       = array();
            $Discount                                       = array();
            $Additional                                     = array();
            foreach ($OrderDetail as $item) {
                $Pricex                                     = $item->price;
                $Discountx                                  = $item->discount;
                $Additionalx                                = $item->additional;
                $DiscountNominalx                           = $Pricex * $Discountx / 100;
                // $Subtotals                                  = $Pricex - $DiscountNominalx + $Additionalx;
                $Subtotals                                  = $Pricex;
                array_push($Subtotal, $Subtotals);
                array_push($Discount, $DiscountNominalx);
                array_push($Additional, $Additionalx);
            }
            $TotalOrderDetail                               = array_sum($Subtotal);
            $TotalOrderDiscount                             = array_sum($Discount);
            $TotalOrderAdditional                           = array_sum($Additional);

            $data                                    = array(
                "status"                                => true,
                "message"                               => 'success',
                "output"                                => array(
                    "ref_number"                            => $Order->ref_number,
                    "date_transaction"                      => DateFormat($Order->date_transaction,"d F Y"),
                    "customer_id"                           => $Order->customer_id,
                    "customer_name"                         => $Order->customer_name,
                    "down_payment"                          => number_format($Order->down_payment,0,",","."),
                    "full_payment"                          => number_format($Order->full_payment,0,",","."),
                    "discount"                              => $TotalOrderDiscount,
                    "nominaldiscount"                       => number_format($TotalOrderDiscount,0,",","."),
                    "additional"                            => number_format($TotalOrderAdditional,0,",","."),
                    "total"                                 => number_format($TotalOrderDetail,0,",","."),
                    "payment_left"                          => number_format($Order->payment_left,0,",","."),
                    "paid"                                  => get_lunas($Order->paid),
                    "type"                                  => get_Typepayment($Order->type),
                    "payment_type"                          => set_PaymentType($Order->payment_type_id),
                    "status"                                => get_statusorder($Order->status)
                )
            );
        }else{
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Sorry, technical error. Please contact your web developer(3)'
            );
        }
        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function repayable($id){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddOrder';

        $Users                                      = Auth::user();
        $Order                                      = OrderModel::find($id);
        $this->_data['order_id']                    = $id;

        $this->_data['Order']                       = $Order;
        $NominalDiscount                            = $Order->total * $Order->discount /100;
        $this->_data['Subtotal']                    = $Order->total - $NominalDiscount + $Order->additional + $Order->shipping_costs;
        $this->_data['Type']                        = get_Typepayment(1);

        $OrderDetail                                = OrderDetailModel::where('order_id','=',$id)->get();
        $this->_data['OrderDetail']                 = $OrderDetail;

        $OrderImage                                 = OrderImageModel::where('order_id','=',$id)->get();
        $this->_data['OrderImage']                  = $OrderImage;


        return Theme::view('modules.order.repayable',$this->_data);
    }

    public function show_imagedetail($order_detail_id){
        $Where                                = array(
            'order_detail_id'                       => $order_detail_id
        );

        $OrderImage                           = OrderImageModel::where($Where)->get();

        $this->_data['OrderImage']            = $OrderImage;

        return Theme::view('modules.order.image',$this->_data);
    }

    public function repay(Request $request){
        $validator = Validator::make($request->all(),[
            'id'                        => 'required',
            'full_payment'              => 'required',
            'payment_left'              => 'required',
            'payment_type'              => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $id                                         = $request->id;
        $FullPayment                                = set_clearFormatRupiah($request->full_payment);
        $Type                                       = 1; #Full Payment#
        $PaymentLeft                                = set_clearFormatRupiah($request->payment_left);
        $PaymentType                                = $request->payment_type;
        $OngkosKirim                                = set_clearFormatRupiah($request->shipping_costs);
        $AlamatKirim                                = $request->shipping_address;
        $Btn                                        = $request->btn;

        $OrderInfo                                  = OrderModel::find($id);
        $Discount                                   = $OrderInfo->discount;
        $NominalDiscount                            = $OrderInfo->total * $Discount / 100;

        $OrderInfoTotal                             = $OrderInfo->total - $NominalDiscount + $OrderInfo->additional;
        if($FullPayment < $OrderInfo->payment_left){
            return redirect()->back()
            ->withInput($request->input())
            ->with('errMsg',"Payment is less than the remaining payment");
        }

        if($OngkosKirim == ""){
            $OngkosKirim                            = 0;
        }

        $Order                                      = OrderModel::find($id);

        if($OngkosKirim > 0){
            $OrderInfoTotal                         = $OrderInfoTotal + $OngkosKirim;
            $Order->total                           = $OrderInfoTotal;
        }

        $Order->shipping_costs                      = $OngkosKirim;
        $Order->shipping_address                    = $AlamatKirim;
        $Order->status                              = 1;

        if($Btn == 'Take_items'){
            $Order->status                          = 2;
            $Order->date_take_items                 = date("Y-m-d H:i:s");
            if($Order->save()){
                $data                                       = array(
                    "scsMsg"                                => 'Invoice <strong>['.$Order->ref_number.']</strong> Berhasil diperbaharui',
                    "invoice"                               => route('order_invoice',$id)
                );
                return redirect()
                    ->route('order_show')
                    ->with($data);
            }
        }

        $Order->full_payment                        = $FullPayment;
        $Order->type                                = 1;
        $TotalPayment                               = $OrderInfo->down_payment + $FullPayment;
        if($TotalPayment >= $OrderInfoTotal){
            $Order->payment_left                    = $OrderInfoTotal - $TotalPayment;
            $Order->paid                            = 1;
            $Order->date_paid                       = date('Y-m-d H:i:s');
        }else{
            return redirect()->back()
            ->withInput($request->input())
            ->with('errMsg',"Sorry, technical error. Please contact your web developer.(5)");
        }
        if($Btn == 'Repay_and_Take'){
            $Order->status                          = 2;
            $Order->date_take_items                      = date("Y-m-d H:i:s");
        }
        $Order->payment_type_full_id                = $PaymentType;
        $Order->updated_by                          = Auth::id();
        if($Order->save()){
            $Users                                          = Auth::user();

            ### CASHBOOK ###
            $CASHBOOK                                       = new CashBookModel;
            $CASHBOOK->debit                                = $FullPayment;
            $CASHBOOK->credit                               = 0;
            $CASHBOOK->ref_id                               = $Order->id;
            $CASHBOOK->flow                                 = "I"; // IN
            $CASHBOOK->status                               = 1; //INCOME
            $CASHBOOK->date_transaction                     = date('Y-m-d H:i:s');
            $CASHBOOK->branch_id                            = $Users->branch_id;
            $CASHBOOK->customer_id                          = $OrderInfo->customer_id;
            $CASHBOOK->flow                                 = 0; // ACTIVE //
            $CASHBOOK->created_by                           = Auth::id();


            if($CASHBOOK->save()){
                if($request->payment_type_full_id == 1){
                    $CodeAccount                        = "KM";
                }else{
                    $CodeAccount                        = "BM";
                }

                $Notransaction                          = $CodeAccount.date("ymd").sprintf("%05s",$CASHBOOK->id);
                $CashBookUpdate                         = CashBookModel::find($CASHBOOK->id);
                $CashBookUpdate->notransaction          = $Notransaction;
                $CashBookUpdate->description            = 'Transaksi dari '.$Notransaction.' oleh '.$Order->customer->name.' dengan pembayaran sebesar Rp '.number_format(set_clearFormatRupiah($FullPayment),0,",",".").',-';
                if($CashBookUpdate->save()){
                    set_SaldoBranch($Users->branch_id,$FullPayment,'IN');
                }
            }
            ### CASHBOOK ###


            $data                                       = array(
                "scsMsg"                                => 'Invoice <strong>['.$Order->ref_number.']</strong> Succesfuly Update',
                "invoice"                               => route('order_invoice',$id)
            );
            return redirect()
                ->route('order_show')
                ->with($data);
        }
    }

    public function set_changedstatus(Request $request){
        $OrderID                                    = $request->order_id;
        $Status                                     = $request->status;


        $Order                                      = OrderModel::find($OrderID);
        $Order->status                              = $Status;
        if($Status == 7){
            if($Order->paid == 1){
                $Order->date_take_items                 = date('Y-m-d H:i:s');
            }
        }

        if($Order->paid == 0 && $Status == 7){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Silakan melakukan pelunasan terlebih dahulu'
            );

        }else{
            if($Order->save()){
                $data                                    = array(
                    "status"                                => true,
                    "message"                               => 'Perubahan status berhasil'
                );
            }else{
                $data                                    = array(
                    "status"                                => false,
                    "message"                               => 'Sorry, technical error. Please contact your web developer(6)'
                );
            }
        }


        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function invoice($id){
        $Users                                          = User::find(Auth::id());
        $Order                                          = OrderModel::find($id);
        $OrderDetail                                    = OrderDetailModel::where("order_id",$id)->get();
        $OrderImage                                     = OrderImageModel::where("order_id",$id)->get();
        $BranchInfo                                     = BranchModel::find($Order->branch_id);

        $Subtotal                                       = array();
        $Discount                                       = array();
        $Additional                                     = array();
        foreach ($OrderDetail as $item) {
            $Pricex                                     = $item->price;
            $Discountx                                  = $item->discount;
            $Additionalx                                = $item->additional;
            $DiscountNominalx                           = $Pricex * $Discountx / 100;
            // $Subtotals                                  = $Pricex - $DiscountNominalx + $Additionalx;
            $Subtotals                                  = $Pricex;
            array_push($Subtotal, $Subtotals);
            array_push($Discount, $DiscountNominalx);
            array_push($Additional, $Additionalx);
        }
        $TotalOrderDetail                               = array_sum($Subtotal);
        $SumAdditional                                  = OrderDetailModel::where("order_id",$id)->sum('additional');

        $this->_data['TotalOrderDetail']                = $TotalOrderDetail;
        $this->_data['Discount_detail']                 = array_sum($Discount);
        $this->_data['Additional_detail']               = array_sum($Additional);


        $this->_data['BranchInfo']                      = $BranchInfo;
        $this->_data['Users']                           = $Users;
        $this->_data['DateNow']                         = date('d F Y');
        $this->_data['Order']                           = $Order;
        $this->_data['Discount']                        = $Order->total * $Order->discount / 100;

        $this->_data['OrderDetail']                     = $OrderDetail;
        $this->_data['OrderImage']                      = $OrderImage;

        $d                                              = new DNS1D();
        $d->setStorPath(__DIR__."/cache/");
        $Format                                         = "62".substr($Order->ref_number,3);
        $Format                                         = ean13_check_digit($Format);
        $this->_data['Barcode']                         = $d->getBarcodeHTML($Format, "EAN13");
        $this->_data['BarcodeCode']                     = $Format;
        // return view('email.invoice',$this->_data);
        return Theme::view('modules.order.print',$this->_data);
    }

    public function details($id){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddOrder';

        $Users                                      = Auth::user();
        $Order                                      = OrderModel::find($id);
        $this->_data['order_id']                    = $id;

        $this->_data['Order']                       = $Order;
        $NominalDiscount                            = $Order->total * $Order->discount /100;
        $this->_data['Subtotal']                    = $Order->total - $NominalDiscount + $Order->additional + $Order->shipping_costs;
        $this->_data['Type']                        = get_Typepayment(1);

        $OrderDetail                                = OrderDetailModel::where('order_id','=',$id)->get();
        $this->_data['OrderDetail']                 = $OrderDetail;

        $OrderImage                                 = OrderImageModel::where('order_id','=',$id)->get();
        $this->_data['OrderImage']                  = $OrderImage;


        return Theme::view('modules.order.details',$this->_data);
    }

    public function get_detailorderinfo(Request $request){
        $order_detail_id                            = $request->order_detail_id;
        $OrderDetailInfo                            = OrderDetailModel::find($order_detail_id);

        if($OrderDetailInfo){
            $data                                    = array(
                "status"                                => true,
                "message"                               => 'success',
                "output"                                => array(
                    "treatment"                             => $OrderDetailInfo->treatment_id,
                    "merk"                                  => $OrderDetailInfo->merk_id,
                    "treatment_category"                    => $OrderDetailInfo->treatment_category_id,
                    "treatment_package"                     => $OrderDetailInfo->treatment_package_id,
                    "price"                                 => number_format($OrderDetailInfo->price,0,",","."),
                    "discount"                              => number_format($OrderDetailInfo->discount,0,",","."),
                    "total"                                 => number_format($OrderDetailInfo->total,0,",","."),
                    "additional"                            => number_format($OrderDetailInfo->additional,0,",","."),
                    "additional_description"                => $OrderDetailInfo->additional_description
                )
            );
        }else{
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Sorry, technical error. Please contact your web developer(3)'
            );
        }
        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function edit_merk(Request $request){
        $order_detail_id                            = $request->order_detail_id;
        $merk                                       = $request->merk;

        $OrderDetail                                = OrderDetailModel::find($order_detail_id);
        if($OrderDetail){
            $MerkInfo                               = MerkModel::find($merk);
            $OrderDetail->merk_id                   = $merk;
            $OrderDetail->merk_name                 = $MerkInfo->name;
            if($OrderDetail->save()){
                $data                                    = array(
                    "status"                                => true,
                    "message"                               => 'Berhasil mengubah merk '.$MerkInfo->name
                );
            }else{
                $data                                    = array(
                    "status"                                => false,
                    "message"                               => 'Sorry, technical error. Please contact your web developer(3)'
                );
            }
        }else{
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Sorry, technical error. Please contact your web developer(3)'
            );
        }
        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function upload_image_done($order_id){
        $OrderInfo                                      = OrderModel::find($order_id);
        $this->_data['Order']                           = $OrderInfo;
        $this->_data['order_id']                        = $order_id;

        $OrderDetail                                    = OrderDetailModel::where('order_id','=',$order_id)->get();
        $this->_data['OrderDetail']                     = $OrderDetail;

        $OrderImage                                     = OrderImageModel::where('order_id','=',$order_id)->get();
        $this->_data['OrderImage']                      = $OrderImage;

        return Theme::view('modules.order.upload',$this->_data);
    }

    public function upload_finish(Request $request){
        $File                                       = $request->file('imageFile');
        $OrderID                                    = $request->order_id;
        $OrderDetailID                              = $request->order_detail_id;
        $Count                                      = count($File);
        $Images                                     = "";
        for($i=0; $i<$Count;$i++){
            $ImageFiles                             = $this->imageUpload($File[$i]);
            if($ImageFiles){
                $OrderImage                         = new OrderImageModel();
                $OrderImage->file                   = $ImageFiles;
                $OrderImage->order_id               = $OrderID;
                $OrderImage->order_detail_id        = $OrderDetailID;
                $OrderImage->created_by             = Auth::id();
                $OrderImage->flag                   = 2;
                $OrderImage->save();
                $Images                            .= '<img src="'.url("/").'/images/item/'.$ImageFiles.'" style="width:50px;height:50px;">';
            }else{
                $data                                    = array(
                    "status"                                => false,
                    "message"                               => 'Sorry, technical error. Please contact your web developer(4)'
                );
                return response($data, 200)
                ->header('Content-Type', 'text/plain');
            }
        }
        $data                                    = array(
            "status"                                => true,
            "order_detail_id"                       => $OrderDetailID,
            "images"                                => $Images,
            "message"                               => 'Success'
        );
        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function form_upload_finish(Request $request){

        $order_id                                       = $request->id;
        $Author                                         = Auth::user();

        $OrderInfo                                      = OrderModel::find($order_id);
        $OrderImage                                     = OrderImageModel::where('order_id','=',$order_id)
                                                                            ->where('flag',2)->count();

//        if($OrderImage == 0){
//            $data                                    = array(
//                "status"                                => false,
//                "message"                               => 'Anda wajib melampirkan foto'
//            );
//            return response($data, 200)
//            ->header('Content-Type', 'text/plain');
//
//        }else{
            $OrderInfo->status                          = 6;
            $OrderInfo->finish_at                       = date('Y-m-d H:i:s');
            $OrderInfo->finish_by                       = Auth::id();
            if($OrderInfo->save()){

                $EmailFormat                            = EmailTemplateModel::where('name','=','NOTIFICATION_DONE')->first();
                $CustomerInfo                           = CustomerModel::find($OrderInfo->customer_id);
                $OrderDetail                            = OrderDetailModel::where('order_id','=',$OrderInfo->id)->get();
                $BranchInfo                             = BranchModel::find($OrderInfo->branch_id);
                if($CustomerInfo->user_id){
                    $Users                              = User::find($CustomerInfo->user_id);
                }else{
                    $Users                              = 0;
                }

                $FULLNAME                               = $CustomerInfo->name;
                $EMAIL                                  = $CustomerInfo->email;
                $DETAIL                                 = '<table cellpadding="1" cellspacing="3" style="border-collapse: collapse;">
                                                                <tr>
                                                                    <td>Treatment</td>
                                                                    <td>Merk</td>
                                                                    <td>Kategori</td>
                                                                    <td>Paket</td>
                                                                    <td>Discount</td>
                                                                    <td>Additional</td>
                                                                    <td>Total</td>
                                                                </tr>';
                foreach ($OrderDetail as $Detail){
                    $DETAIL                             .= '<tr>
                                                                <td>'.$Detail->treatment->name.'</td>
                                                                <td>'.$Detail->merk->name.'</td>';
                    if($Detail->treatment_category_id){
                        $DETAIL                             .= '<td>'.$Detail->treatmentcategory->name.'</td>';
                    }else{
                        $DETAIL                             .= '<td>-</td>';
                    }
                    $DETAIL                                 .= '<td>'.$Detail->treatment_package.'</td>';
                    if($Detail->discount > 0){
                        $DETAIL                             .= '<td>'.set_numberformat($Detail->price * $Detail->discount / 100).'</td>';
                    }else{
                        $DETAIL                             .= '<td>-</td>';
                    }

                    if($Detail->additional > 0){
                        $DETAIL                             .= '<td>'.set_numberformat($Detail->additional).'</td>';
                    }else{
                        $DETAIL                             .= '<td>-</td>';
                    }
                    $DETAIL                             .= '
                                                                <td>'.set_numberformat($Detail->total).'</td>
                                                            </tr>
                                                            </table>';
                }

                $CABANG                                 =   '<strong>'.$BranchInfo->name.'</strong><br>'.
                                                            $BranchInfo->address.'<br>
                                                            Telp : '. $BranchInfo->phone;

                $Body                                   = $EmailFormat->template;
                $Body                                   = str_replace("@FULLNAME",$FULLNAME,$Body);
                $Body                                   = str_replace("@EMAIL",$EMAIL,$Body);
                $Body                                   = str_replace("@DETAIL",$DETAIL,$Body);
                $Body                                   = str_replace("@CABANG",$CABANG,$Body);

                $EmailParams                            = array(
                    'Subject'                               => $Author->name." dari Yourbagspa.com",
                    'Views'                                 => "email.notification_done",
                    'User'                                  => $Users,
                    'To'                                    => $EMAIL,
                    'Body'                                  => $Body,
                    'Password'                              => get_default_password(),
                    'attachment'                            => '' // required
                );
//                dd($EmailParams);
                dispatch(new SendMail($EmailParams));

                $data                                    = array(
                    "status"                                => true,
                    "message"                               => 'Set Order Finish Berhasil',
                    "output"                                => $OrderInfo
                );
            }

            return response($data, 200)
            ->header('Content-Type', 'text/plain');
//        }

    }
}
