<?php

namespace App\Modules\Event\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Event\Models\Event as EventModel;
use App\Modules\Branch\Models\BranchModel;
use Auth;
use Theme;


class EventController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:event-view']);
        $this->middleware('permission:event-add')->only('add');
        $this->middleware('permission:event-edit')->only('edit');
        $this->middleware('permission:event-delete')->only('delete');

        $this->_data['string_menuname']             = 'Event';
        $this->_data['IDMENU']                      = 'Event';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'event';
        $this->_data['IDSUBMENU']                   = 'ListEvent';

        return Theme::view('modules.event.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'event';
        $this->_data['IDSUBMENU']                   = 'ListEvent';
        $this->_data['DateReset']                   = date('d-m-Y');

        return Theme::view('modules.event.show',$this->_data);
    }

    public function datatables(){
        $Users                                      = Auth::user();
        $BranchID                                   = $Users->branch_id;

        if($Users->can('access-pusat')){
            $Event = EventModel::join('branchs','branchs.id','=','events.branch_id')
                                ->select(['events.id', 'events.name', 'events.address', 'events.phone', 'branchs.name as branch','events.is_active', 'events.created_at', 'events.updated_at']);
        }else{
            $Event = EventModel::join('branchs','branchs.id','=','events.branch_id')
                                ->select(['events.id', 'events.name', 'events.address', 'events.phone', 'branchs.name as branch','events.is_active', 'events.created_at', 'events.updated_at'])
                                ->where('branch_id','=',$BranchID);
        }

        return Datatables::of($Event)
            ->addColumn('href', function ($Event) {
                return '<a href="'.route('event_edit',$Event->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$Event->id.')"><i class="fa fa-ban"></i></a>&nbsp;
                        <a href="javascript:void(0);" class="btn btn-warning" onclick="ResetSaldo('.$Event->id.')"><i class="glyphicon glyphicon-refresh"></i></a>&nbsp;&nbsp;
                        ';
            })

            ->editColumn('is_active', function ($Event) {
                if($Event->is_active == 1){
                    return '<span class="label label-sm label-success">'.get_active($Event->is_active).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_active($Event->is_active).'</span>';
                }
            })

            ->rawColumns(['href','address','is_active'])
            ->make(true);
    }


    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddEvent';

        $this->_data['Users']                       = Auth::user();

        return Theme::view('modules.event.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddEvent';

        $this->_data['id']                          = $request->id;
        $Event                                      = EventModel::find($request->id);

        $this->_data['Event']                       = $Event;
        $this->_data['BranchID']                    = $Event->branch_id;
        $this->_data['Users']                       = Auth::user();

        return Theme::view('modules.event.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'branch'    => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Users                                      = Auth::user();

        $Event                                      = new EventModel();
        $Event->name                                = $request->name;
        $Event->address                             = $request->address;
        $Event->phone                               = $request->phone;
        if(bool_CheckAccessUser('access-pusat')){
            $Event->branch_id                       = $request->branch;
        }else{
            $Event->branch_id                       = $Users->branch_id;
        }
        $Event->saldo                               = 0;
        $Event->saldo_start                         = date('Y-m-d H:i:s');
        $Event->saldo_realtime                      = 0;
        $Event->saldo_realtime_date                 = date('Y-m-d H:i:s');
        $Event->created_by                          = Auth::id();
        $Event->is_active                           = 1;

        if($Event->save()){
            return redirect()
                ->route('event_show')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'branch'    => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $id                                         = $request->id;
        $Event                                      = EventModel::find($request->id);
        $Event->name                                = $request->name;
        $Event->address                             = $request->address;
        $Event->phone                               = $request->phone;
        $Event->updated_by                         = Auth::id();
        $Event->is_active                          = 1;

        if($Event->save()){
            return redirect()
                ->route('event_show')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function delete(Request $request){
        $Event                                     = EventModel::find($request->id);
        $Event->is_active                          = 0;
        $Event->updated_by                         = Auth::id();
        if($Event->save()){
            return redirect()
                ->route('event_show')
                ->with('scsMsg',"Event succesful inactive");
        }else{
            dd("Error data inactive");
        }
    }

    public function resetsaldo(Request $request){
        $SaldoAwal                                  = set_clearFormatRupiah($request->saldoawal);
        $EventID                                    = $request->id_reset;

        if($SaldoAwal == ""){
            $data                                    = array(
                "status"                                => false,
                "message"                               => 'Saldo harus diisi.'
            );
        }else{
            $Event                                 = EventModel::find($EventID);
            $Event->saldo                          = $SaldoAwal;
            $Event->saldo_start                    = DateFormat($request->date_reset,"Y-m-d H:i:s");
            $Event->saldo_realtime                 = $SaldoAwal;
            $Event->saldo_realtime_date            = DateFormat($request->date_reset,"Y-m-d H:i:s");
            if($Event->save()){
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
        $EventID                                   = $request->event_id;
        $EventInfo                                 = EventModel::find($EventID);

        if($EventInfo){
            $data                                    = array(
                "status"                                => true,
                "message"                               => 'OK',
                "output"                                => array(
                    "event"                             => $EventInfo,
                    "saldo_realtime"                    => number_format($EventInfo->saldo_realtime,0,",","."),
                    "saldo_realtime_date"               => DateFormat($EventInfo->saldo_realtime_date,"d F Y H:i:s")
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

    public function searchbybranch(Request $request){
        $BranchID                                       = $request->branch_id;
        $Where                                          = array(
            "is_active"                                 => 1,
            "branch_id"                                 => $BranchID
        );
        $Event                                       = EventModel::where($Where)->get();

            echo '<option value="0">Choose Event</option>';
        foreach($Event as $item){
            echo '<option value="'.$item->id.'">' . $item->name . '</option>';
        }
    }


}
