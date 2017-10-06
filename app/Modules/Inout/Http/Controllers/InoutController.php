<?php

namespace App\Modules\Inout\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Account\Models\Account as AccountModel;
use App\Modules\Inout\Models\Inout as InoutModel;
use App\Modules\Cashbook\Models\CashBookModel;

use Auth;
use Theme;
use Activity;


class InoutController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:finance-menu']);
        $this->middleware(['permission:inout-view']);
        $this->middleware('permission:inout-add')->only('add');
        $this->middleware('permission:inout-edit')->only('edit');
        $this->middleware('permission:inout-inactive')->only('inactive');

        $this->_data['string_menuname']             = 'Income/Expense';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'inout';

        return Theme::view('modules.inout.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'inout';

        return Theme::view('modules.inout.show',$this->_data);
    }

    public function datatables(){
        $Inout = InoutModel::join('accounts','accounts.id','=','inouts.account_id')
            ->select(['inouts.id', 'inouts.name','accounts.name as account', 'inouts.total','inouts.date_transaction'])
            ->where('inouts.is_active','=',1);

        return Datatables::of($Inout)
            ->addColumn('href', function ($Inout) {
                $btn =  '<a href="'.route('inout_edit',$Inout->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;';
                    $btn .=   '<a href="javascript:void(0);" class="btn btn-danger" onclick="inactiveList('.$Inout->id.')"><i class="fa fa-ban"></i></a>&nbsp';
                return $btn;
            })

            ->editColumn('date_transaction', function ($Inout) {
                return DateFormat($Inout->date_transaction,'d/m/Y');
            })

            ->editColumn('total', function ($Inout) {
                return number_format($Inout->total,0,",",".");
            })

            ->rawColumns(['href','date_transaction','total'])
            ->make(true);
    }


    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['date_transaction']            = date('d-m-Y');

        return Theme::view('modules.inout.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'              => 'required',
            'flow'              => 'required',
            'account'           => 'required',
            'total'             => 'required',
            'date_transaction'  => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $User                                           = Auth::user();

        if($User->can('access-pusat')) {
            $BranchID                                   = $request->branch;
        }else{
            $BranchID                                   = $User->branch_id;
        }
        $Total                                          = set_clearFormatRupiah($request->total);
        $Inout                                          = new InoutModel();
        $Inout->name                                    = $request->name;
        $Inout->account_id                              = $request->account;
        $Inout->total                                   = $Total;
        $Inout->date_transaction                        = DateFormat($request->date_transaction,"Y-m-d");
        $Inout->payment_type_id                         = $request->payment_type;
        $Inout->branch_id                               = $BranchID;
        $Inout->created_by                              = Auth::id();
        $Inout->updated_by                              = Auth::id();

        try{
            if($Inout->save()){
                $AccountInfo                            = AccountModel::find($request->account);
                $Flow                                   = $AccountInfo->flow;

                ### CASHBOOK ###
                $CASHBOOK                                       = new CashBookModel;
                if($Flow == "I"){
                    $CASHBOOK->debit                            = $Total;
                    $CASHBOOK->credit                           = 0;
                }else{
                    $CASHBOOK->debit                            = 0;
                    $CASHBOOK->credit                           = $Total;
                }
                $CASHBOOK->ref_id                               = $Inout->id;
                $CASHBOOK->flow                                 = $Flow; // IN
                if($Flow == "I"){
                    $CASHBOOK->status                               = 4; //INCOME
                }else{
                    $CASHBOOK->status                               = 5; //EXPENSE
                }
                $CASHBOOK->date_transaction                     = DateFormat($request->date_transaction,"Y-m-d");
                $CASHBOOK->branch_id                            = $BranchID;
                $CASHBOOK->customer_id                          = 0;
                $CASHBOOK->flow                                 = 0; // ACTIVE //
                $CASHBOOK->created_by                           = Auth::id();
                $CASHBOOK->url                                  = '/inout/details/';
                $CASHBOOK->event_id                             = 0;


                if($CASHBOOK->save()){
                    if($Flow == "I"){
                        if($request->payment_type == 1){
                            $CodeAccount                        = "KM";
                        }else{
                            $CodeAccount                        = "BM";
                        }
                    }else{
                        if($request->payment_type == 1){
                            $CodeAccount                        = "KK";
                        }else{
                            $CodeAccount                        = "BK";
                        }

                    }

                    $Notransaction                          = $CodeAccount.date("ymd").sprintf("%05s",$CASHBOOK->id);
                    $CashBookUpdate                         = CashBookModel::find($CASHBOOK->id);
                    $CashBookUpdate->notransaction          = $Notransaction;
                    $CashBookUpdate->description            = 'No. Transaksi '.$Notransaction.' Keperluan '.$request->name.' sebesar Rp '.number_format(set_clearFormatRupiah($Total),0,",",".").',-';
                    if($CashBookUpdate->save()){
                        if($Flow == "I"){
                            set_SaldoBranch($BranchID,$Total,'IN');
                        }else{
                            set_SaldoBranch($BranchID,$Total,'OUT');
                        }
                    }
                }
                ### CASHBOOK ###


                return redirect()
                    ->route('inout_show')
                    ->with('ScsMsg',"Data Berhasil tersimpan");
            }
        }catch (\Exception $e) {
            $Detail                                     = array(
                "name"                                  => $request->name,
                "flow"                                  => $request->flow,
                "account_id"                            => $request->account,
                "total"                                 => $request->total,
                "date_transaction"                      => $request->date_transaction,
                "branch_id"                             => $BranchID
            );
            Activity::log([
                'contentId'   => 0,
                'contentType' => 'Inout Save',
                'action'      => 'post',
                'description' => $e->getMessage(),
                'details'     => json_encode($Detail),
                'updated'     => Auth::id(),
            ]);

            return redirect()
                ->route('inout_add')
                ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer.")
                ->withInput($request->input());
        }
    }


    public function edit($id){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';

        $this->_data['id']                          = $id;
        $Inout                                      = InoutModel::find($id);
        $AccountInfo                                = AccountModel::find($Inout->account_id);

        $this->_data['BranchID']                    = $Inout->branch_id;
        $this->_data['Inout']                       = $Inout;
        $this->_data['Flow']                        = $AccountInfo->flow;
        $this->_data['date_transaction']            = DateFormat($Inout->date_transaction,"d-m-Y");

        return Theme::view('modules.inout.form',$this->_data);
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name'              => 'required',
            'flow'              => 'required',
            'account'           => 'required',
            'total'             => 'required',
            'date_transaction'  => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }


        $User                                           = Auth::user();

        if($User->can('access-pusat')) {
            $BranchID                                   = $request->branch;
        }else{
            $BranchID                                   = $User->branch_id;
        }
        $Total                                          = set_clearFormatRupiah($request->total);
        $id                                             = $request->id;
        $Inout                                          = InoutModel::find($id);
        $Inout->name                                    = $request->name;
        $Inout->total                                   = $Total;
        $Inout->updated_by                              = Auth::id();

        try{
            $Inout->save();
            $AccountInfo                                = AccountModel::find($request->account);
            $Flow                                       = $AccountInfo->flow;

            ### CASHBOOK ###
            if($Flow == "I"){
                $StatusTransaction                      = 4; //INCOME
            }else{
                $StatusTransaction                      = 5; //EXPENSE
            }

            $CASHBOOKPREV                               = CashBookModel::where('status','=',$StatusTransaction)
                                                                        ->where('ref_id','=',$id)->first();
            if($CASHBOOKPREV){
                if($Flow == "I") {
                    $TotalPrev = $CASHBOOKPREV->debit;
                }else{
                    $TotalPrev = $CASHBOOKPREV->credit;
                }
                if($TotalPrev != $Total){
                    if($Flow == "I"){
                        set_SaldoBranch($BranchID,$TotalPrev,'OUT');
                    }else{
                        set_SaldoBranch($BranchID,$TotalPrev,'IN');
                    }

                    $CASHBOOKNEW                            = CashBookModel::find($CASHBOOKPREV->id);
                    if($Flow == "I"){
                        $CASHBOOKNEW->debit                 = $Total;
                        $CASHBOOKNEW->credit                = 0;
                    }else{
                        $CASHBOOKNEW->debit                 = 0;
                        $CASHBOOKNEW->credit                = $Total;
                    }
                    $CASHBOOKNEW->description            = 'No. Transaksi '.$CASHBOOKPREV->notransaction.' Keperluan '.$request->name.' sebesar Rp '.number_format(set_clearFormatRupiah($Total),0,",",".").',-';

                    try{
                        $CASHBOOKNEW->save();
                        if($Flow == "I"){
                            set_SaldoBranch($BranchID,$Total,'IN');
                        }else{
                            set_SaldoBranch($BranchID,$Total,'OUT');
                        }

                        return redirect()
                            ->route('inout_show')
                            ->with('ScsMsg',"Data Berhasil tersimpan");
                    }catch (\Exception $e) {
                        $Detail                                     = array(
                            "id"                                    => $id,
                            "name"                                  => $request->name,
                            "total"                                 => $request->total
                        );
                        Activity::log([
                            'contentId'   => $id,
                            'contentType' => 'Inout Update',
                            'action'      => 'update',
                            'description' => $e->getMessage(),
                            'details'     => json_encode($Detail),
                            'updated'     => Auth::id(),
                        ]);
                        return redirect()
                            ->route('inout_edit',$id)
                            ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer.")
                            ->withInput($request->input());
                    }
                }else{
                    return redirect()
                        ->route('inout_edit',$id)
                        ->with('WrngMsg',"Total anda sama dengan total sebelumnya.")
                        ->withInput($request->input());
                }
            }
            ### END CASHBOOK ###


        }catch (\Exception $e) {
            $Detail                                     = array(
                "id"                                    => $id,
                "name"                                  => $request->name,
                "flow"                                  => $request->flow,
                "account_id"                            => $request->account,
                "total"                                 => $request->total,
                "date_transaction"                      => $request->date_transaction,
                "branch_id"                             => $BranchID
            );
            Activity::log([
                'contentId'   => $id,
                'contentType' => 'Inout Update',
                'action'      => 'update',
                'description' => $e->getMessage(),
                'details'     => json_encode($Detail),
                'updated'     => Auth::id(),
            ]);

            return redirect()
                ->route('inout_edit',$id)
                ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer.")
                ->withInput($request->input());
        }
    }

    public function inactive($id){
        $Inout                                      = InoutModel::find($id);
        $Inout->is_active                           = 0;
        $Inout->updated_by                          = Auth::id();
        try{
            $Inout->save();

            ### CASHBOOK ###
            $AccountInfo                            = AccountModel::find($Inout->account_id);
            $Flow                                   = $AccountInfo->flow;


            if($Flow == "I"){
                $StatusTransaction                      = 4; //INCOME
            }else{
                $StatusTransaction                      = 5; //EXPENSE
            }

            $CASHBOOKPREV                               = CashBookModel::where('status','=',$StatusTransaction)
                                                                        ->where('ref_id','=',$id)->first();

            if($CASHBOOKPREV){
                if($Flow == "I") {
                    $TotalPrev                          = $CASHBOOKPREV->debit;
                }else{
                    $TotalPrev                          = $CASHBOOKPREV->credit;
                }
                $BranchID                               = $Inout->branch_id;

                if($Flow == "I"){
                    set_SaldoBranch($BranchID,$TotalPrev,'OUT');
                }else{
                    set_SaldoBranch($BranchID,$TotalPrev,'IN');
                }

                $CASHBOOKNEW                            = CashBookModel::find($CASHBOOKPREV->id);
                $CASHBOOKNEW->flag                      = 1;
                $CASHBOOKNEW->save();
            }
            ### CASHBOOK ###

            return redirect()
                ->route('inout_show')
                ->with('ScsMsg',"Income/Expense Berhasil dinonaktifkan");
        }catch (\Exception $e) {
            $Detail = array(
                "id"                    => $id,
                "is_active"             => 1
            );
            Activity::log([
                'contentId'   => $id,
                'contentType' => 'Inout Inactive',
                'action'      => 'inactive',
                'description' => $e->getMessage(),
                'details'     => json_encode($Detail),
                'updated'     => Auth::id(),
            ]);
            return redirect()
                ->route('inout_show')
                ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer.");
        }
    }


}
