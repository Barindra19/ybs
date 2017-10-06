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
use App\Modules\Order\Models\OrderItemModel;
use App\Modules\Order\Models\OrderItemDetail as OrderItemDetailModel;
use App\Modules\Stock\Models\Stock as StockModel;

use App\Modules\Role\Models\Role;
use App\Modules\Cashbook\Models\CashBookModel;

use App\Jobs\SendMail;
use Illuminate\Auth\Events\Registered;
use App\User;

class OrderItemsController extends Controller
{

    protected $_data = array();
    protected $destinationPath = array();

    public function __construct()
    {
        $this->middleware(['permission:order-menu-view']);
        $this->middleware('permission:order-add')->only('add');

        $this->destinationPath = public_path('images/item');

        $this->_data['string_menuname']             = 'Order Items';
        $this->_data['IDMENU']                      = 'Order Items';
    }

    public function index(){

    }

    public function show(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'List';
        $this->_data['IDSUBMENU']                   = 'ListOrder';

        $Users                                      = Auth::user();

        return Theme::view('modules.order.items.show',$this->_data);
    }


    public function datatables(){
        $Users                          = Auth::user();
        $daysago                        = date('c', strtotime('-60 days'));
        $twoMonth                       = DateFormat($daysago,"Y-m-d");

        if($Users->can('access-pusat')){
            $Order = OrderItemModel::join('branchs','branchs.id','=','order_items.branch_id')
                                ->select(['order_items.id', 'branchs.name as branch','order_items.ref_number as code','order_items.date_transaction','order_items.customer_name as customer', 'order_items.total'])
                                ->where('order_items.status',1)
                                ->where('order_items.created_by','<=',$twoMonth);
        }else{
            $Order = OrderItemModel::join('branchs','branchs.id','=','order_items.branch_id')
                                ->select(['order_items.id', 'branchs.name as branch','order_items.ref_number as code','order_items.date_transaction','order_items.customer_name as customer', 'order_items.total'])
                                ->where('order_items.status',1)
                                ->where('order_items.created_by','<=',$twoMonth);
        }



        return Datatables::of($Order)
            ->addColumn('href', function ($Order) {
                return '<a href="javascript:void(0);" class="btn btn-info" onclick="viewOrder('.$Order->id.')"><i class="glyphicon glyphicon-search"></i></a>';
            })

            ->editColumn('date_transaction', function ($Order) {
                    return DateFormat($Order->date_transaction,"d/m/Y H:i:s");
            })

            ->editColumn('status', function ($Order) {
                if($Order->status == 1){
                    return '<span class="label label-sm label-success">'.get_active_user($Order->status).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_active_user($Order->status).'</span>';
                }
            })
            ->editColumn('total', '{{ number_format($total,0,",",".") }}')
            ->rawColumns(['href','paid','status'])
            ->make(true);
    }

    public function new($CustomerID){
        $Customer                                   = CustomerModel::find($CustomerID);

        $Users                                      = Auth::user();
        $Order                                      = new OrderItemModel;
        $Order->date_transaction                    = date('Y-m-d H:i:s');
        $Order->branch_id                           = $Users->branch_id;
        if($Customer){
            $Order->customer_id                         = $Customer->customer;
            $Order->customer_name                       = $Customer->name;
        }
        $Order->total                               = 0;
        $Order->discount                            = 0;
        $Order->additional                          = 0;
        $Order->created_by                          = Auth::id();
        $Order->status                              = 0;

        if($Order->save()){
            $OrderUpdate                            = OrderItemModel::find($Order->id);
            $OrderUpdate->ref_number                = "YBSI".date("ymd").sprintf("%04s",$Order->id);
            $OrderUpdate->save();
            return redirect()
                ->route('order_items_add',$Order->id)
                ->with('infoMsg',"Complete this form");
        }

    }

    public function add($OrderID){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddOrder';

        $OrderInfo                                  = OrderItemModel::find($OrderID);
        $CustomerInfo                               = CustomerModel::find($OrderInfo->customer_id);

        $Users                                      = Auth::user();
        $OrderItemDetailModelInfo                   = OrderItemDetailModel::where('order_item_id',$OrderID)->get();
        $SumOrderItemDetail                         = OrderItemDetailModel::where('order_item_id',$OrderID)->sum('total');

        $this->_data['DateNow']                     = Date("d-m-Y");
        $this->_data['OrderInfo']                   = $OrderInfo;
        $this->_data['OrderItemDetail']             = $OrderItemDetailModelInfo;
        $this->_data['Users']                       = $Users;
        $this->_data['BranchID']                    = $Users->branch_id;
        $this->_data['Order_item_id']               = $OrderInfo->id;
        $this->_data['TotalDetail']                 = number_format($SumOrderItemDetail,0,",",".");


        return Theme::view('modules.order.items.add',$this->_data);
    }



    public function calculate(Request $request){
        $Quantity                                   = set_clearFormatRupiah($request->quantity);
        $Price                                      = set_clearFormatRupiah($request->price);
        $SubTotal                                   = $Quantity * $Price;
        $Discount                                   = set_clearFormatRupiah($request->discount);
        $Additional                                 = set_clearFormatRupiah($request->additional);
        $NominalDiscount                            = $SubTotal * $Discount / 100;
        $data                                       = array(
                "total"                             => number_format($SubTotal - $NominalDiscount + $Additional,0,",","."),
                "subtotal"                          => number_format($SubTotal,0,",","."),
                "nominaldiscount"                   => number_format($NominalDiscount,0,",","."),
        );

        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function calculate_price(Request $request){
        $Quantity                                   = set_clearFormatRupiah($request->quantity);
        $Price                                      = set_clearFormatRupiah($request->price);
        $SubTotal                                   = $Quantity * $Price;
        $Discount                                   = set_clearFormatRupiah($request->discount);
        $Additional                                 = set_clearFormatRupiah($request->additional);
        $NominalDiscount                            = $SubTotal * $Discount / 100;
        $data                                       = array(
                "total"                             => number_format($SubTotal - $NominalDiscount + $Additional,0,",","."),
                "subtotal"                          => number_format($SubTotal,0,",","."),
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
        $Stock                                      = $request->stock;
        $ReadyStock                                 = $request->readystock;
        $Quantity                                   = $request->quantity;
        $Price                                      = $request->price;
        $Discount                                   = $request->discount;
        $Additional                                 = $request->additional;
        $Total                                      = $request->total;
        $OrderID                                    = $request->order_item_id;

        if($Stock == 0){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Nama Barang wajib diisi'
            );
        }else if($Quantity == 0 || $Quantity == ""){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Jumlah wajib diisi'
            );
        }else if($ReadyStock < $Quantity){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Stock anda hanya '.$ReadyStock." pcs"
            );
        }else{
            $StockInfo                              = StockModel::find($Stock);
            $StockName                              = $StockInfo->name;

            $OrderItemDetail                        = new OrderItemDetailModel();
            $OrderItemDetail->stock_id              = $Stock;
            $OrderItemDetail->quantity              = $Quantity;
            $OrderItemDetail->price                 = set_clearFormatRupiah($Price);
            $OrderItemDetail->discount              = set_clearFormatRupiah($Discount);
            $OrderItemDetail->additional            = set_clearFormatRupiah($Additional);
            $OrderItemDetail->total                 = set_clearFormatRupiah($Total);
            $OrderItemDetail->order_item_id         = $OrderID;
            $OrderItemDetail->created_by            = Auth::id();
            $OrderItemDetail->save();


            $DisplayPrice                           = set_clearFormatRupiah($Price);
            $DisplayDiscount                        = set_clearFormatRupiah($Discount);
            $DisplayAdditional                      = set_clearFormatRupiah($Additional);
            $DisplayTotal                           = set_clearFormatRupiah($Total);

            $OrderItemInfo                          = OrderItemModel::find($OrderID);
            $HeaderTotal                            = $OrderItemInfo->total;
            $Total                                  = $HeaderTotal + $DisplayTotal;
            $OrderItemInfo->total                   = $Total;
            $OrderItemInfo->save();

            $data                                    = array(
                "status"                                => true,
                "message"                               => 'Item succesful add',
                "output"                                => array(
                    "name"                              => $OrderItemDetail->stock->name,
                    "quantity"                          => $OrderItemDetail->quantity,
                    "price"                             => number_format($DisplayPrice,0,",","."),
                    "nominaldiscount"                   => number_format($DisplayPrice * $DisplayDiscount / 100,0,",","."),
                    "discount"                          => $DisplayDiscount,
                    "additional"                        => number_format($DisplayAdditional,0,",","."),
                    "total"                             => number_format($DisplayTotal,0,",","."),
                    "total_header"                      => number_format($Total,0,",","."),
                    "id"                                => $OrderItemDetail->id
                )
            );
        }

        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function deletedetail(Request $request){
        $id                                         = $request->id;
        $OrderItemDetail                            = OrderItemDetailModel::find($id);
        $OrderItemID                                = $OrderItemDetail->order_item_id;
        $Subtotal                                   = $OrderItemDetail->total;

        if($OrderItemDetail->delete()){
            $OrderItem                              = OrderItemModel::find($OrderItemID);
            $TotalUpdate                            = $OrderItem->total - $Subtotal;
            $OrderItem->total                       = $OrderItem->total - $Subtotal;
            $OrderItem->save();
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

    public function save(Request $request){
        $validator = Validator::make($request->all(), [
            'id'                        => 'required',
//            'customer'                  => 'required',
            'total_header'              => 'required',
            'grandtotal'                => 'required',
            'payment'                   => 'required',
            'payment_type'              => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $Order_items_id                             = $request->id;
        $Total                                      = set_clearFormatRupiah($request->total_header);
        $DiscountHeader                             = set_clearFormatRupiah($request->discount_header);
        $AdditionalHeader                           = set_clearFormatRupiah($request->additional_header);
        $GrandTotal                                 = set_clearFormatRupiah($request->grandtotal);
        $Payment                                    = set_clearFormatRupiah($request->payment);
        $PaymentType                                = $request->payment_type;

        if($DiscountHeader == ""){
            $DiscountHeader                         = 0;
        }
        if($AdditionalHeader == ""){
            $AdditionalHeader                       = 0;
        }
        $Users                                      = User::find(Auth::id());
        $OrderItem                                  = OrderItemModel::find($Order_items_id);
        $OrderItem->total                           = $GrandTotal;
        $OrderItem->discount                        = $DiscountHeader;
        $OrderItem->additional                      = $AdditionalHeader;
        if($request->customer){
            $Customer                               = $request->customer;
            $CustomerName                           = $OrderItem->customer->name;
        }else{
            $Customer                               = 0;
            $CustomerName                           = "Tanpa Nama";
        }
        $OrderItem->customer_id                     = $Customer;
        $OrderItem->customer_name                   = $CustomerName;
        $OrderItem->payment                         = $Payment;
        $OrderItem->payment_type_id                 = $PaymentType;
        $OrderItem->branch_id                       = $Users->branch_id;
        $OrderItem->date_transaction                = date("Y-m-d H:i:s");
        $OrderItem->status                          = 1;
        $OrderItem->created_by                      = Auth::id();
        $OrderItem->updated_by                      = Auth::id();

        if($OrderItem->save()){
            $OrderItemDetail                        = OrderItemDetailModel::where('order_item_id',$OrderItem->id)->get();
            if($OrderItemDetail){
                $Laba                               = array();
                $Subtotal                           = array();
                $Discount                           = array();
                $Additional                         = array();
                foreach ($OrderItemDetail as $item) {
                    if($Customer > 0){
                        if($item->stock->point > 0){
                            set_TransactionPoint($Customer,$item->stock->point,'IN');
                        }
                    }
                    $StockInfo                      = StockModel::find($item->stock_id);
                    $LabaBersih                     = $StockInfo->selling_price - $StockInfo->cost_of_good;
                    $LabaBersihTotal                = $LabaBersih * $item->quantity;
                    $HargaTotalDiscount             = ($item->price * $item->discount / 100) * $item->quantity;
                    $SetLaba                        = $LabaBersihTotal - $HargaTotalDiscount;
                    array_push($Laba,$SetLaba);

                    $StockOld                       = $StockInfo->stock;
                    $StockNow                       = $StockOld - $item->quantity;
                    $StockInfo->stock               = $StockNow;
                    $StockInfo->save();

                    $Pricex                                     = $item->price;
                    $Discountx                                  = $item->discount;
                    $Additionalx                                = $item->additional;
                    $DiscountNominalx                           = $Pricex * $Discountx / 100;
                    $Subtotals                                  = $item->total;
                    array_push($Subtotal, $Subtotals);
                    array_push($Discount, $DiscountNominalx);
                    array_push($Additional, $Additionalx);
                }

                $Debit                              = array_sum($Laba);
                ### CASHBOOK ###
                $CASHBOOK                                       = new CashBookModel;
                $CASHBOOK->debit                                = $Debit;
                $CASHBOOK->credit                               = 0;
                $CASHBOOK->ref_id                               = $OrderItem->id;
                $CASHBOOK->flow                                 = "I"; // IN
                $CASHBOOK->status                               = 3; //Order
                $CASHBOOK->date_transaction                     = date('Y-m-d H:i:s');
                $CASHBOOK->branch_id                            = $Users->branch_id;
                $CASHBOOK->customer_id                          = $Customer;
                $CASHBOOK->flow                                 = 0; // ACTIVE //
                $CASHBOOK->created_by                           = Auth::id();


                if($CASHBOOK->save()){
                    if($PaymentType == 1){
                        $CodeAccount                        = "KM";
                    }else{
                        $CodeAccount                        = "BM";
                    }

                    $Notransaction                          = $CodeAccount.date("ymd").sprintf("%05s",$CASHBOOK->id);
                    $CashBookUpdate                         = CashBookModel::find($CASHBOOK->id);
                    $CashBookUpdate->notransaction          = $Notransaction;
                    $CashBookUpdate->description            = 'Transaksi dari '.$Notransaction.' oleh '.$CustomerName.' dengan pembayaran sebesar Rp '.number_format(set_clearFormatRupiah($Payment),0,",",".").' dengan Laba bersih sebesar Rp '.number_format(set_clearFormatRupiah($Debit),0,",",".").',-';
                    if($CashBookUpdate->save()){
                        set_SaldoBranch($Users->branch_id,$Debit,'IN');
                    }
                }
                ### CASHBOOK ###

                if($Customer > 0){
                    ### Send mail
                    $TotalOrderItemDetail                           = array_sum($Subtotal);
                    $EmailParams                            = array(
                        'Subject'                               => $Users->name." dari Your Bag Spa",
                        'Views'                                 => "email.invoice_items",
                        'Users'                                 => $Users,
                        'To'                                    => $OrderItem->customer->email,
                        'DateNow'                               => date('d F Y'),
                        'Order'                                 => $OrderItem,
                        'Discount'                              => array_sum($Discount),
                        'TotalOrderDetail'                      => $TotalOrderItemDetail,
                        'OrderDetail'                           => $OrderItemDetail,
                        'attachment'                            => ""
                    );
                    dispatch(new SendMail($EmailParams));
                    ### Send mail
                }

                $data                                       = array(
                    "scsMsg"                                => 'Invoice <strong>['.$OrderItem->ref_number.']</strong> Berhasil terbentuk',
                    "invoice"                               => route('order_items_invoice',$Order_items_id)
                );
                return redirect()
                    ->route('order_items_show')
                    ->with($data);

            }
        }
    }

    public function get_detailorder(Request $request){

        $id                                         = $request->order_item_id;
        $Order                                      = OrderItemModel::find($id);
        if($Order){
            $OrderDetail                                    = OrderItemDetailModel::where("order_item_id",$id)->get();
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
                    "customer_name"                         => $Order->customer->name,
                    "payment"                               => number_format($Order->payment,0,",","."),
                    "discount"                              => $TotalOrderDiscount,
                    "nominaldiscount"                       => number_format($TotalOrderDiscount,0,",","."),
                    "additional"                            => number_format($TotalOrderAdditional,0,",","."),
                    "total"                                 => number_format($TotalOrderDetail,0,",","."),
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


    public function invoice($order_detail_id){
        $Users                                          = User::find(Auth::id());
        $OrderItem                                      = OrderItemModel::find($order_detail_id);
        $OrderItemDetail                                = OrderItemDetailModel::where("order_item_id",$order_detail_id)->get();
        $Subtotal                                       = array();
        $Discount                                       = array();
        $Additional                                     = array();

        foreach ($OrderItemDetail as $item) {
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
        $TotalOrderItemDetail                           = array_sum($Subtotal);
        $SumAdditional                                  = OrderItemDetailModel::where("order_item_id",$order_detail_id)->sum('additional');

        $this->_data['TotalOrderDetail']                = $TotalOrderItemDetail;
        $this->_data['Discount_detail']                 = array_sum($Discount);
        $this->_data['Additional_detail']               = array_sum($Additional);

        $this->_data['Users']                           = $Users;
        $this->_data['DateNow']                         = date('d F Y');
        $this->_data['Order']                           = $OrderItem;
        $this->_data['Discount']                        = $OrderItem->total * $OrderItem->discount / 100;

        $this->_data['OrderDetail']                     = $OrderItemDetail;


        return Theme::view('modules.order.items.print',$this->_data);

    }
}
