<?php

namespace App\Modules\Customer\Http\Controllers;

use App\Modules\Emailtemplate\Models\EmailTemplate;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Customer\Models\CustomerModel;
use App\Modules\Branch\Models\BranchModel;
use App\Modules\Order\Models\OrderModel;
use App\Modules\User\Models\UserModel;


use App\Modules\Role\Models\Role;
use App\Jobs\SendMail;
use Illuminate\Auth\Events\Registered;
use App\User;
use PDF;
use Auth;
use Theme;

use \Milon\Barcode\DNS1D;



class CustomerController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:customer-menu-view']);
        $this->middleware(['permission:customer-view']);
        $this->middleware('permission:customer-add')->only('add');
        $this->middleware('permission:customer-edit')->only('edit');
        $this->middleware('permission:customer-delete')->only('delete');

        $this->_data['string_menuname']             = 'Customer';
        $this->_data['IDMENU']                      = 'Customer';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'customer';
        $this->_data['IDSUBMENU']                   = 'ListCustomer';

        return Theme::view('modules.customer.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'customer';
        $this->_data['IDSUBMENU']                   = 'ListCustomer';

        return Theme::view('modules.customer.show',$this->_data);
    }

    public function datatables(){
        $Users                                      = Auth::user();
        $BranchID                                   = $Users->branch_id;
        if($Users->can('access-pusat')){
            $Customer = CustomerModel::join('branchs','branchs.id','=','customers.branch_id')
                                        ->select(['customers.id', 'customers.ref_number as code', 'customers.name', 'customers.phone', 'customers.email', 'branchs.name as branch', 'customers.created_at as join_date'])
                                        ->where('customers.status','=',1);

        }else{
            $Customer = CustomerModel::join('branchs','branchs.id','=','customers.branch_id')
                                        ->select(['customers.id', 'customers.ref_number as code', 'customers.name', 'customers.phone', 'customers.email', 'branchs.name as branch', 'customers.created_at as join_date'])
                                        ->where('customers.branch_id','=',$BranchID)
                                        ->where('customers.status','=',1);
        }

        return Datatables::of($Customer)
            ->addColumn('href', function ($Customer) {
                return '<a href="'.route('customer_edit',$Customer->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$Customer->id.')"><i class="glyphicon glyphicon-trash"></i></a>
                        <a href="'.route('customer_generate_barcode',$Customer->id).'" class="btn btn-success" target="_blank"><i class="fa fa-barcode"></i></a>
                        ';
            })
            // ->editColumn('icon', '<i class="{{$icon}}"></i>')
            ->rawColumns(['href','icon'])
            ->make(true);
    }

    public function datatables_list(){
            $Customer = CustomerModel::join('branchs','branchs.id','=','customers.branch_id')
                                        ->select(['customers.id', 'customers.ref_number as code', 'customers.name', 'customers.email', 'branchs.name as branch'])
                                        ->where('customers.status','=',1);

        return Datatables::of($Customer)
            ->addColumn('href', function ($Customer) {
                return '<a href="javascript:void(0);" class="btn btn-warning" onclick="GetData('.$Customer->id.')"><i class="glyphicon glyphicon-ok "></i></a>';
            })
            ->rawColumns(['href'])
            ->make(true);
    }

    public function datatables_detail($CustomerID){
        $Order = OrderModel::select(['orders.id','orders.ref_number as code','orders.date_transaction', 'orders.down_payment',  'orders.full_payment as pay', 'orders.payment_left as payment','orders.paid','orders.status'])
            ->where('orders.customer_id','=', $CustomerID)
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


    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddCustomer';

        $Users                                      = Auth::user();

        $BranchID                                   = $Users->branch_id;
        $this->_data['BranchID']                    = $BranchID;

        return Theme::view('modules.customer.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddCustomer';

        $this->_data['id']                          = $request->id;
        $Customer                                   = CustomerModel::find($request->id);

        $this->_data['Customer']                    = $Customer;

//        $d                                          = new DNS1D();
//        $d->setStorPath(__DIR__."/cache/");
//        $Format                                     = "628".date('y').substr($Customer->ref_number,4);
//        $Format                                     = ean13_check_digit($Format);
//        $this->_data['Barcode']                     = $d->getBarcodeHTML($Format, "EAN13");


        return Theme::view('modules.customer.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'address' => 'required',
            // 'mobile' => 'required',
            'email' => 'required|email|unique:users',
            'branch' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Customer                                   = new CustomerModel;
        $Customer->name                             = $request->name;
        $Customer->address                          = $request->address;
        $Customer->phone                            = $request->phone;
        $Customer->mobile                           = $request->mobile;
        $Customer->email                            = $request->email;
        $Customer->branch_id                        = $request->branch;
        $Customer->status                           = 1;
        $Customer->created_by                       = Auth::id();

        if($Customer->save()){
            event(new Registered($Users = $this->create($request)));
            $Role                                   = Role::where('name', 'customer')->first();
            $Users->attachRole($Role->id); // Customer ID //

            $EmailFormat                            = EmailTemplate::where('name','=','VERIFICATION_REGISTER_CUSTOMER')->first();
            $LinkVerification                       = '<a href="'.route("users_request_reset_password",$Users->email_token).'" > '.route("users_request_reset_password",$Users->email_token).'</a>';
            $Body                                   = $EmailFormat->template;
            $Body                                   = str_replace("@FULLNAME",$Users->name,$Body);
            $Body                                   = str_replace("@EMAIL",$Users->email,$Body);
            $Body                                   = str_replace("@LINKVERIFICATION",$LinkVerification,$Body);

            $EmailParams                            = array(
                'Subject'                               => "Selamat datang di Your Bag Spa",
                'Views'                                 => "email.verification_register_customer",
                'User'                                  => $Users,
                'To'                                    => $Users->email,
                'Body'                                  => $Body,
                'Password'                              => get_default_password(),
                'attachment'                            => '' // Required
            );

            dispatch(new SendMail($EmailParams));
            $CustomerCode                           = substr($Customer->id,-3);
            $Code                                   = "CUST".date("my").sprintf("%03s",$CustomerCode);
//            $Code                                   = date("ymd").sprintf("%04s",$Customer->id);
            $CustomerUpdate                         = CustomerModel::find($Customer->id);
            $CustomerUpdate->ref_number             = $Code;
            $CustomerUpdate->user_id                = $Users->id;
            $CustomerUpdate->save();

            if($request->btn == 'NextOrder'){
                return redirect()
                    ->route('order_add',$Customer->id)
                    ->with('infoMsg',"Please Next your Transaction");
            }
            return redirect()
                ->route('customer_show')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    protected function create($request){


        return User::create([
            'name'                  => $request->name,
            'email'                 => $request->email,
            'password'              => bcrypt(get_default_password()),
            'is_active'             => 1,
            'is_lock'               => 0,
            'email_token'           => base64_encode($request->email),
            'branch_id'             => $request->branch

        ]);
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            // 'mobile' => 'required',
            'email' => 'required|email',
            'branch' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $id                                         = $request->id;

        $Customer                                   = CustomerModel::find($request->id);
        $Customer->name                             = $request->name;
        $Customer->address                          = $request->address;
        $Customer->phone                            = $request->phone;
        $Customer->mobile                           = $request->mobile;
        $Customer->email                            = $request->email;
        $Customer->branch_id                        = $request->branch;
        $Customer->status                           = 1;
        $Customer->updated_by                       = Auth::id();
        if($Customer->save()){
            return redirect()
                ->route('customer_show')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function delete(Request $request){
        $Customer                                   = CustomerModel::find($request->id);
        $Customer->status                           = 0;
        $Customer->updated_by                       = Auth::id();
        if($Customer){
            if($Customer->save()){
                return redirect()
                    ->route('customer_show')
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

    public function getdetail(Request $request){
        $CustomerID                                     = $request->customer;
        $Customer                                       = CustomerModel::find($CustomerID);
        if($Customer){
            $data                                    = array(
                "status"                                => true,
                "output"                                => $Customer,
                "customer_select"                       => '<option value="'.$CustomerID.'">'.$Customer->name.' ['.$Customer->branch->name.']</option>'

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

    public function add_customer(Request $request){

        $Name                       = $request->name_customer;
        $Address                    = $request->address;
        $Phone                      = $request->phone;
        $Mobile                     = $request->mobile;
        $Email                      = $request->email;
        $BranchID                   = $request->branch;

        if($Name == ""){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Nama wajib diisi.',
                "field"                                 => 'name'
            );
            return response($data, 200)
            ->header('Content-Type', 'text/plain');

        }else if($Address == ""){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Alamat wajib diisi',
                "field"                                 => 'address'
            );
            return response($data, 200)
            ->header('Content-Type', 'text/plain');
        }else if($Email == ""){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Email wajib diisi',
                "field"                                 => 'email'
            );
            return response($data, 200)
            ->header('Content-Type', 'text/plain');
        }else if(filter_var($Email, FILTER_VALIDATE_EMAIL) == FALSE){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Format email salah!',
                "field"                                 => 'email'
            );
            return response($data, 200)
            ->header('Content-Type', 'text/plain');
        }else if(UserModel::where('email',$Email)->count() > 0){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Email anda sudah terdaftar!',
                "field"                                 => 'email'
            );
            return response($data, 200)
            ->header('Content-Type', 'text/plain');
        }else{
            $Users                                      = Auth::user();
            $Customer                                   = new CustomerModel;
            $Customer->name                             = $request->name_customer;
            $Customer->address                          = $request->address;
            $Customer->phone                            = $request->phone;
            $Customer->mobile                           = $request->mobile;
            $Customer->email                            = $request->email;
            $Customer->branch_id                        = $Users->branch_id;
            $Customer->status                           = 1;
            $Customer->created_by                       = Auth::id();

            if($Customer->save()){
                event(new Registered($Users = $this->create($request)));
                $Role                                   = Role::where('name', 'customer')->first();
                $Users->attachRole($Role->id); // Customer ID //

                $EmailFormat                            = EmailTemplate::where('name','=','VERIFICATION_REGISTER_CUSTOMER')->first();
                $LinkVerification                       = '<a href="'.route("users_request_reset_password",$Users->email_token).'" > '.route("users_request_reset_password",$Users->email_token).'</a>';
                $Body                                   = $EmailFormat->template;
                $Body                                   = str_replace("@FULLNAME",$Users->name,$Body);
                $Body                                   = str_replace("@EMAIL",$Users->email,$Body);
                $Body                                   = str_replace("@LINKVERIFICATION",$LinkVerification,$Body);


                $EmailParams                            = array(
                    'Subject'                               => "Selamat datang di Your Bag Spa",
                    'Views'                                 => "email.verification_register_customer",
                    'User'                                  => $Users,
                    'To'                                    => $Users->email,
                    'Body'                                  => $Body,
                    'Password'                              => get_default_password(),
                    'attachment'                            => '' // required
                );

                dispatch(new SendMail($EmailParams));
                $CustomerCode                           = substr($Customer->id,-3);
                $Code                                   = "CUST".date("my").sprintf("%03s",$CustomerCode);
//                $Code                                   = date("ymd").sprintf("%04s",$Customer->id);
                $CustomerUpdate                         = CustomerModel::find($Customer->id);
                $CustomerUpdate->ref_number             = $Code;
                $CustomerUpdate->user_id                = $Users->id;
                $CustomerUpdate->save();

                $CustomerInfo                           = CustomerModel::find($Customer->id);
                $data                                    = array(
                    "status"                                => true,
                    "message"                               => 'Customer berhasil ditambahkan',
                    "output"                                => $CustomerInfo,
                    "customer_select"                       => '<option value="'.$CustomerInfo->id.'">'.$CustomerInfo->name.' ['.$CustomerInfo->branch->name.']</option>'
                );
                return response($data, 200)
                ->header('Content-Type', 'text/plain');

            }
        }


    }

    public function list_by_popup(){
        return Theme::view('modules.customer.list',$this->_data);
    }

    public function generate_barcode($id){
        $Customer                                   = CustomerModel::find($id);

        $d                                          = new DNS1D();
        $d->setStorPath(__DIR__."/cache/");
        $Format                                     = "628".date('y').substr($Customer->ref_number,4);
        $Format                                     = ean13_check_digit($Format);
        $data['BarcodePNG']                         = $d->getBarcodePNG($Format, "EAN13");
        $data['Barcode']                            = $Format;
        $pdf = PDF::loadView('pdf.barcode', $data);
        return $pdf->download($Format.'.pdf');

        // echo  '<img src="data:image/png;base64,' .  . '" alt="barcode"   />';
    }

    public function detail_customer($CustomerID){
        $Customer                                       = CustomerModel::find($CustomerID);
        $OrderActive                                    = OrderModel::where('customer_id','=',$CustomerID)
            ->where('status','<',7)
            ->where('orders.invoice','>', 0)
            ->count();
        $OrderLunas                                     = OrderModel::where('customer_id','=',$CustomerID)
            ->where('paid','=',1)
            ->where('orders.invoice','>', 0)
            ->count();

        $OrderSum                                       = OrderModel::where('customer_id','=',$CustomerID)
            ->where('orders.invoice','>', 0)
            ->sum('total');

//            echo Auth::id();
//            dd($OrderActive);
        $this->_data['OrderActive']                     = $OrderActive;
        $this->_data['CustomePoint']                    = $Customer->point;
        $this->_data['OrderSum']                        = $OrderSum;
        $this->_data['OrderLunas']                      = $OrderLunas;
        $this->_data['CustomerID']                      = $CustomerID;


        return Theme::view('modules.customer.detail',$this->_data);
    }

    public function search_autocomplete(Request $request){
        $Key                        = $request->term['term'];
        $BranchID                   = $request->branch_id;
        $Customer                   = CustomerModel::where('status','=',1)->where('branch_id','=',$BranchID)->where('name','LIKE', "%{$Key}%")->get();

        $x                          = 0;
        $Arr                        = array();
        foreach ($Customer as $item){
            $Arr[$x]['id']            = $item->id;
            $Arr[$x]['name']          = $item->name;
            $Arr[$x]['email']         = $item->email;
            $Arr[$x]['branch']        = $item->branch->name;
            $x++;
        }

        return json_encode($Arr);
    }

    public function get_detail(Request $request){
        $CustomerID                     = $request->id;
        $Customer                       = CustomerModel::find($CustomerID);
        $Data                           = array(
            "status"                => true,
            "output"                => array(
                "name"                  => $Customer->name,
                "email"                 => $Customer->email,
                "ref_number"            => $Customer->ref_number,
                "branch"                => $Customer->branch->name
            )
        );

        return response($Data, 200)
            ->header('Content-Type', 'text/plain');
    }
}
