<?php

namespace App\Http\Controllers;

use App\Modules\Barcode\Models\BarcodeList;
use Illuminate\Http\Request;
use Theme;
use Yajra\Datatables\Facades\Datatables;


use Auth;
use App\User;
use App\Modules\Cashbook\Models\CashBookModel;
use App\Modules\Branch\Models\BranchModel;
use App\Modules\Customer\Models\CustomerModel;
use App\Modules\Order\Models\OrderModel;
use App\Modules\Order\Models\OrderDetailModel;

use \Milon\Barcode\DNS1D;


class HomeController extends Controller
{
    protected $_data = array();

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Users                                             = Auth::user();
        if(bool_CheckUserRole('customer') != TRUE){
            $DateFrom                                       = date('Y-m-d 00:00:00');
            $DateTo                                         = date('Y-m-d 23:59:59');

            $datePrevFrom                                   = date_create($DateFrom);
            date_add($datePrevFrom,date_interval_create_from_date_string("-1 days"));
            $datePrevFrom                                   = date_format($datePrevFrom,"Y-m-d H:i:s");


            $datePrevTo                                     = date_create($DateTo);
            date_add($datePrevTo,date_interval_create_from_date_string("-1 days"));
            $datePrevTo                                     = date_format($datePrevTo,"Y-m-d H:i:s");


            $datePrevTo                                     = strtotime($DateTo);
            $datePrevTo                                     = strtotime("-1 day", $datePrevTo);

            $TransactionDebitInBranchToday                  = CashBookModel::where('branch_id','=',$Users->branch_id)
                                                                            ->where('date_transaction','>=',$DateFrom)
                                                                            ->where('date_transaction','<=',$DateTo)
                                                                            ->sum('debit');

            $CountTransactionInBranchToday                  = CashBookModel::where('branch_id','=',$Users->branch_id)
                                                                            ->where('date_transaction','>=',$DateFrom)
                                                                            ->where('date_transaction','<=',$DateTo)
                                                                            ->count();

            $CountNewCustomerTodayInBranch                  = CustomerModel::where('branch_id','=',$Users->branch_id)
                                                                            ->where('created_at','>=',$DateFrom)
                                                                            ->where('created_at','<=',$DateTo)
                                                                            ->count();

            $CountNewCustomerYesterdayInBranch              = CustomerModel::where('branch_id','=',$Users->branch_id)
                                                                            ->where('created_at','>=',$datePrevFrom)
                                                                            ->where('created_at','<=',$datePrevTo)
                                                                            ->count();



            $Branch                                         = BranchModel::find($Users->branch_id);

            $this->_data['TransactionDebitTodayInMyBranch'] = number_format($TransactionDebitInBranchToday);
            $this->_data['CountTransactionInBranchToday']   = number_format($CountTransactionInBranchToday);
            $this->_data['CountNewCustomerTodayInBranch']   = number_format($CountNewCustomerTodayInBranch);
            $this->_data['CountNewCustomerYesterdayInBranch']= $CountNewCustomerYesterdayInBranch;


            ### CHART TRANSACTION ###
            $dateSevenDays                                  = date_create(date('Y-m-d'));
            date_add($dateSevenDays,date_interval_create_from_date_string("-7 days"));

        	$dateSevenDays                                 = date_format($dateSevenDays,"Y-m-d");
        	$dateSevenDaysEnd                              = date("Y-m-d");


            $DataLastWeek                                 = array();
            $i                                             = 0;
        	while (strtotime($dateSevenDays) <= strtotime($dateSevenDaysEnd)) {
                $DateChartFrom                              = dateFormat($dateSevenDays,"Y-m-d 00:00:00");
                $DateChartTo                                = dateFormat($dateSevenDays,"Y-m-d 23:59:59");

                $TransactionDebitInBranchChart              = CashBookModel::where('branch_id','=',$Users->branch_id)
                                                                                ->where('date_transaction','>=',$DateChartFrom)
                                                                                ->where('date_transaction','<=',$DateChartTo)
                                                                                ->sum('debit');

                $DataLastWeek[$i]["date"]                           = dateFormat($dateSevenDays,"d F");
                $DataLastWeek[$i]["income"]                         = $TransactionDebitInBranchChart;
                $dateSevenDays = date ("Y-m-d", strtotime("+1 day", strtotime($dateSevenDays)));
                $i++;
        	}

            $this->_data['DataLastWeek']                    = $DataLastWeek;
            ### CHART TRANSACTION ###


            $this->_data['Branch']                          = $Branch->name;
            $this->_data['BranchID']                        = $Branch->id;
        }

        if(bool_CheckUserRole('customer') == TRUE){
            $Customer                                       = CustomerModel::where('user_id','=',Auth::id())->first();
            $OrderActive                                    = OrderModel::where('customer_id','=',$Customer->id)
                                                                            ->where('status','<',7)
                                                                            ->where('orders.invoice','>', 0)
                                                                            ->count();
            $OrderLunas                                     = OrderModel::where('customer_id','=',$Customer->id)
                                                                        ->where('paid','=',1)
                                                                        ->where('orders.invoice','>', 0)
                                                                        ->count();

            $OrderSum                                       = OrderModel::where('customer_id','=',$Customer->id)
                                                                        ->where('orders.invoice','>', 0)
                                                                        ->sum('total');

//            echo Auth::id();
//            dd($OrderActive);
            $this->_data['OrderActive']                     = $OrderActive;
            $this->_data['CustomePoint']                    = $Customer->point;
            $this->_data['OrderSum']                        = $OrderSum;
            $this->_data['OrderLunas']                      = $OrderLunas;

        }

        return Theme::view('home',$this->_data);
    }

    public function datatables(){
        $Users                          = Auth::user();
        $Customer                       = CustomerModel::where('user_id','=',Auth::id())->first();

        $Order = OrderModel::select(['orders.id','orders.ref_number as code','orders.date_transaction', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status'])
                            ->where('orders.customer_id','=', $Customer->id)
                            ->where('orders.invoice','>', 0)
                            ->orderBy('orders.date_transaction','DESC');

        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="'. route('order_details_customer',$Order->id).'" class="btn btn-info"><i class="glyphicon glyphicon-search"></i></a>';
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
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }

    public function by_branch($BranchID){
        $Users                                          = Auth::user();

        if(bool_CheckUserRole('customer') != TRUE){
            $DateFrom                                       = date('Y-m-d 00:00:00');
            $DateTo                                         = date('Y-m-d 23:59:59');

            $datePrevFrom                                   = date_create($DateFrom);
            date_add($datePrevFrom,date_interval_create_from_date_string("-1 days"));
            $datePrevFrom                                   = date_format($datePrevFrom,"Y-m-d H:i:s");


            $datePrevTo                                     = date_create($DateTo);
            date_add($datePrevTo,date_interval_create_from_date_string("-1 days"));
            $datePrevTo                                     = date_format($datePrevTo,"Y-m-d H:i:s");


            $datePrevTo                                     = strtotime($DateTo);
            $datePrevTo                                     = strtotime("-1 day", $datePrevTo);

            $TransactionDebitInBranchToday                  = CashBookModel::where('branch_id','=',$BranchID)
                                                                            ->where('date_transaction','>=',$DateFrom)
                                                                            ->where('date_transaction','<=',$DateTo)
                                                                            ->sum('debit');

            $CountTransactionInBranchToday                  = CashBookModel::where('branch_id','=',$BranchID)
                                                                            ->where('date_transaction','>=',$DateFrom)
                                                                            ->where('date_transaction','<=',$DateTo)
                                                                            ->count();

            $CountNewCustomerTodayInBranch                  = CustomerModel::where('branch_id','=',$BranchID)
                                                                            ->where('created_at','>=',$DateFrom)
                                                                            ->where('created_at','<=',$DateTo)
                                                                            ->count();

            $CountNewCustomerYesterdayInBranch              = CustomerModel::where('branch_id','=',$BranchID)
                                                                            ->where('created_at','>=',$datePrevFrom)
                                                                            ->where('created_at','<=',$datePrevTo)
                                                                            ->count();



            $Branch                                         = BranchModel::find($BranchID);

            $this->_data['TransactionDebitTodayInMyBranch'] = number_format($TransactionDebitInBranchToday);
            $this->_data['CountTransactionInBranchToday']   = number_format($CountTransactionInBranchToday);
            $this->_data['CountNewCustomerTodayInBranch']   = number_format($CountNewCustomerTodayInBranch);
            $this->_data['CountNewCustomerYesterdayInBranch']= $CountNewCustomerYesterdayInBranch;


            ### CHART TRANSACTION ###
            $dateSevenDays                                  = date_create(date('Y-m-d'));
            date_add($dateSevenDays,date_interval_create_from_date_string("-7 days"));

        	$dateSevenDays                                  = date_format($dateSevenDays,"Y-m-d");
        	$dateSevenDaysEnd                               = date("Y-m-d");


            $DataLastWeek                                   = array();
            $i                                              = 0;
        	while (strtotime($dateSevenDays) <= strtotime($dateSevenDaysEnd)) {
                $DateChartFrom                              = dateFormat($dateSevenDays,"Y-m-d 00:00:00");
                $DateChartTo                                = dateFormat($dateSevenDays,"Y-m-d 23:59:59");

                $TransactionDebitInBranchChart              = CashBookModel::where('branch_id','=',$BranchID)
                                                                                ->where('date_transaction','>=',$DateChartFrom)
                                                                                ->where('date_transaction','<=',$DateChartTo)
                                                                                ->sum('debit');

                $DataLastWeek[$i]["date"]                           = dateFormat($dateSevenDays,"d F");
                $DataLastWeek[$i]["income"]                         = $TransactionDebitInBranchChart;
                $dateSevenDays = date ("Y-m-d", strtotime("+1 day", strtotime($dateSevenDays)));
                $i++;
        	}

            $this->_data['DataLastWeek']                    = $DataLastWeek;
            ### CHART TRANSACTION ###

            $this->_data['Branch']                          = $Branch->name;
            $this->_data['BranchID']                        = $BranchID;
        }
        return Theme::view('home',$this->_data);
    }


    public function checkTransaction(Request $request){
        $CheckString                                = strpos($request->barcode,"*");
        if($CheckString > 0){
            $data                                   = array(
                "status"                                => false,
                "message"                               => 'Format Salah, Barcode terdiri dari 13 Digit.'
            );
        }else{

            $Barcode                                = $request->barcode;
# Result Customer CUST3170472
# Customer 62817 03170472

//            $NoCustomer                             = "CUST".substr($Barcode,5,7);
            $NoOrder                                = "YBS".substr($Barcode,2,10);
            $CountOrder                             = OrderModel::where('ref_number','=',$NoOrder)->count();

            ### CHECK ORDER ###

            if($CountOrder > 0){
                $OrderInfo                                      = OrderModel::where('ref_number','=',$NoOrder)->first();
                $OrderDetail                                    = OrderDetailModel::where("order_id",$OrderInfo->id)->get();

                $Subtotal                                       = array();
                $Discount                                       = array();
                $Additional                                     = array();
                foreach ($OrderDetail as $item) {
                    $Pricex                                     = $item->price;
                    $Discountx                                  = $item->discount;
                    $Additionalx                                = $item->additional;
                    $DiscountNominalx                           = $Pricex * $Discountx / 100;
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
                    "message"                               => 'OK',
                    "checkresult"                           => 'order',
                    "output"                                => array(
                        "ref_number"                            => $OrderInfo->ref_number,
                        "date_transaction"                      => DateFormat($OrderInfo->date_transaction,"d F Y"),
                        "customer_id"                           => $OrderInfo->customer_id,
                        "customer_name"                         => $OrderInfo->customer_name,
                        "down_payment"                          => number_format($OrderInfo->down_payment,0,",","."),
                        "full_payment"                          => number_format($OrderInfo->full_payment,0,",","."),
                        "discount"                              => $TotalOrderDiscount,
                        "nominaldiscount"                       => number_format($TotalOrderDiscount,0,",","."),
                        "additional"                            => number_format($TotalOrderAdditional,0,",","."),
                        "total"                                 => number_format($TotalOrderDetail,0,",","."),
                        "payment_left"                          => number_format($OrderInfo->payment_left,0,",","."),
                        "paid"                                  => get_lunas($OrderInfo->paid),
                        "type"                                  => get_Typepayment($OrderInfo->type),
                        "payment_type"                          => set_PaymentType($OrderInfo->payment_type_id),
                        "status"                                => get_statusorder($OrderInfo->status),
                        "order_id"                              => $OrderInfo->id
                    )
                );
            }else{### END CHECK ORDER ### =============== ### CHECK CUSTOMER ###
                $BarcodeList                            = BarcodeList::where('barcode','=',$Barcode)->first();
                if($BarcodeList->customer_id){
                    $CountCustomer                          = CustomerModel::where('id','=',$BarcodeList->customer_id)->count();
                }else{
                    $CountCustomer                          = 0;
                }
                if($CountCustomer > 0){
                    $CustomerInfo                           = CustomerModel::find($BarcodeList->customer_id);
                    $data                                    = array(
                        "status"                                => true,
                        "message"                               => 'OK',
                        "checkresult"                           => 'customer',
                        "output"                                => array(
                            "ref_number"                            => $CustomerInfo->ref_number,
                            "customer_id"                           => $CustomerInfo->id,
                            "name"                                  => $CustomerInfo->name,
                            "address"                               => $CustomerInfo->address,
                            "phone"                                 => $CustomerInfo->phone,
                            "mobile"                                => $CustomerInfo->mobile,
                            "email"                                 => $CustomerInfo->email
                        )
                    );
                }else{ ### END CHECK CUSTOMER ### ============= ### BARCODE TIDAK DITEMUKAN ###
                    $data                                   = array(
                        "status"                                => false,
                        "customer"                              => $Barcode,
                        "order"                                 => $NoOrder,
                        "message"                               => 'Barcode Tidak Ditemukan!'
                    );
                }
            }
        }

        // 6217090804058
        // 6281703170472
        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

}
