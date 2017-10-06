<?php

namespace App\Modules\Account\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Account\Models\Account as AccountModel;
use Auth;
use Theme;
use Activity;

class AccountController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:account-view']);
        $this->middleware('permission:account-add')->only('add');
        $this->middleware('permission:account-edit')->only('edit');
        $this->middleware('permission:account-inactive')->only('inactive');

        $this->_data['string_menuname']             = 'Account';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'account';

        return Theme::view('modules.account.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'account';

        return Theme::view('modules.account.show',$this->_data);
    }

    public function datatables(){
        $Account = AccountModel::select(['id', 'name', 'flow','is_active']);

        return Datatables::of($Account)
            ->addColumn('href', function ($Account) {
                $btn =  '<a href="'.route('account_edit',$Account->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;';
                if($Account->is_active == 0){
                 $btn .=   '<a href="javascript:void(0);" class="btn btn-success" onclick="activateList('.$Account->id.')"><i class="fa fa-check"></i></a>&nbsp';
                }else if ($Account->is_active == 1){
                  $btn .=  '<a href="javascript:void(0);" class="btn btn-danger" onclick="inactiveList('.$Account->id.')"><i class="fa fa-ban"></i></a>&nbsp';
                }
                return $btn;
            })

            ->editColumn('flow', function ($Account) {
                if($Account->flow == 'I'){
                    return '<span class="label label-sm label-success">Pendapatan</span>';
                }else{
                    return '<span class="label label-sm label-danger">Pengeluaran</span>';
                }
            })

            ->editColumn('is_active', function ($Account) {
                if($Account->is_active == 1){
                    return '<span class="label label-sm label-success">'.get_active($Account->is_active).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_active($Account->is_active).'</span>';
                }
            })

            ->rawColumns(['href','flow','is_active'])
            ->make(true);
    }

    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';

        return Theme::view('modules.account.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'flow'      => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Account                                        = new AccountModel();
        $Account->name                                  = $request->name;
        $Account->flow                                  = $request->flow;
        $Account->is_active                             = 1;
        $Account->created_by                            = Auth::id();
        $Account->updated_by                            = Auth::id();

        try{
            if($Account->save()){
                return redirect()
                    ->route('account_show')
                    ->with('ScsMsg',"Data Berhasil tersimpan");
            }
        }catch (\Exception $e) {
            $Detail                                     = array(
                "name"                                  => $request->name,
                "flow"                                  => $request->flow
            );
            Activity::log([
                'contentId'   => 0,
                'contentType' => 'Account Save',
                'action'      => 'post',
                'description' => $e->getMessage(),
                'details'     => json_encode($Detail),
                'updated'     => Auth::id(),
            ]);
            return redirect()
                ->route('account_add')
                ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer.")
                ->withInput($request->input());
        }
    }

    public function edit($id){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';

        $this->_data['id']                          = $id;
        $Account                                    = AccountModel::find($id);

        $this->_data['Account']                     = $Account;

        return Theme::view('modules.account.form',$this->_data);
    }


    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'flow'      => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $id                                             = $request->id;
        $Account                                        = AccountModel::find($id);
        $Account->name                                  = $request->name;
        $Account->flow                                  = $request->flow;
        $Account->is_active                             = 1;
        $Account->updated_by                            = Auth::id();

        try{
            if($Account->save()){
                return redirect()
                    ->route('account_show')
                    ->with('ScsMsg',"Data Berhasil diperbaharui");
            }
        }catch (\Exception $e) {
            $Detail                                     = array(
                "id"                                    => $id,
                "name"                                  => $request->name,
                "flow"                                  => $request->flow
            );
            Activity::log([
                'contentId'   => $id,
                'contentType' => 'Account Update',
                'action'      => 'update',
                'description' => $e->getMessage(),
                'details'     => json_encode($Detail),
                'updated'     => Auth::id(),
            ]);
            return redirect()
                ->route('account_edit',$id)
                ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer.")
                ->withInput($request->input());
        }
    }


    public function inactive($id){
        $Account                                    = AccountModel::find($id);
        $Account->is_active                         = 0;
        $Account->updated_by                        = Auth::id();
        try{
            $Account->save();
            return redirect()
                ->route('account_show')
                ->with('ScsMsg',"Account Berhasil dinonaktifkan");
        }catch (\Exception $e) {
            $Detail = array(
                "id"                    => $id,
                "is_active"             => 1
            );
            Activity::log([
                'contentId'   => $id,
                'contentType' => 'Account Inactive',
                'action'      => 'inactive',
                'description' => $e->getMessage(),
                'details'     => json_encode($Detail),
                'updated'     => Auth::id(),
            ]);
            return redirect()
                ->route('account_show')
                ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer.");
        }
    }

    public function activate($id){
        $Account                                    = AccountModel::find($id);
        $Account->is_active                         = 1;
        $Account->updated_by                        = Auth::id();
        try{
            $Account->save();
            return redirect()
                ->route('account_show')
                ->with('ScsMsg',"Account Berhasil diaktifkan");
        }catch (\Exception $e) {
            $Detail = array(
                "id"                    => $id,
                "is_active"             => 1
            );
            Activity::log([
                'contentId'   => $id,
                'contentType' => 'Account Activate',
                'action'      => 'activate',
                'description' => $e->getMessage(),
                'details'     => json_encode($Detail),
                'updated'     => Auth::id(),
            ]);
            return redirect()
                ->route('account_show')
                ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer.");
        }
    }

    public function get_listbyflow(Request $request){
        $Flow                                           = $request->flow;
        $Where                                          = array(
            "is_active"                                     => 1,
            "flow"                                          => $Flow
        );
        $Account                                        = AccountModel::where($Where)->get();

        foreach($Account as $item){
            echo '<option value="'.$item->id.'">' . $item->name . '</option>';
        }

    }
}
