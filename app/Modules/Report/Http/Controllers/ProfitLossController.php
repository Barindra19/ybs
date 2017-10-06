<?php

namespace App\Modules\Report\Http\Controllers;

use App\Modules\Branch\Models\BranchModel;
use App\Modules\Cashbook\Models\CashBookModel;
use App\Modules\Status\Models\StatusModel;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use Theme;
use Excel;
use PDF;


class ProfitLossController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:report-menu']);
        $this->middleware(['permission:report-profitloss-view']);

        $this->_data['string_menuname']             = 'Profit & Loss';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'Report';
        $this->_data['Users']                       = Auth::user();

        return Theme::view('modules.report.profitloss.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'Report';
        $this->_data['IDSUBMENU']                   = 'ReportProfitLoss';
        $this->_data['Users']                       = Auth::user();
        $this->_data['Month']                       = date('n');
        $this->_data['Year']                        = date('Y');
        $this->_data['BranchID']                    = Auth::user()->branch_id;


        return Theme::view('modules.report.profitloss.show',$this->_data);
    }

    public function retrieve($get)
    {
        $Get                                        = explode(" | ", base64_decode($get));
        $Month                                      = $Get[1];
        $Year                                       = $Get[2];
        $Action                                     = $Get[3];

        $Users                                      = Auth::user();

        $dateStart                                  = $Year."-".$Month."-01";
        $dateEnd                                    = $Year."-".$Month."-".DateFormat($dateStart,"t");

        $Where                                      = array();
        if(bool_CheckAccessUser('access-pusat')){
            $BranchID                                   = $Get[0];
        }else{
            $BranchID                                   = $Users->branch_id;
        }
        $BranchInfo                                     = BranchModel::find($BranchID);
        ### STATUS LOOPING ###
        $StatusInfos                                    = StatusModel::all();
        $x                                              = 0;
        foreach($StatusInfos as $StatusInfo){
            $Status[$x]['id']                             = $StatusInfo->id;
            $Status[$x]['name']                           = $StatusInfo->name;
            $Status[$x]['description']                    = $StatusInfo->description;
            $x++;
        }
        ### STATUS LOOPING ###

        $ArrResult                                      = array();
        $i                                              = 0;
        foreach ($Status as $item_status){
            ### ORDER ###
            if($item_status['id'] == 1){
                $OrderTrx                               = CashBookModel::where('status','=',1)
                                                                        ->where('flag','=',0)
                                                                        ->where('branch_id','=',$BranchID)
                                                                        ->where('date_transaction','>=',$dateStart)
                                                                        ->where('date_transaction','<=',$dateEnd)
                                                                        ->get();
                if($OrderTrx){
                    $ArrOrderTrxDebit                   = array();
                    $ArrOrderTrxCredit                  = array();
                    foreach ($OrderTrx as $Order){
                        if($Order->debit > 0 && $Order->credit == 0){
                            array_push($ArrOrderTrxDebit,$Order->debit);
                        }else if($Order->debit == 0 && $Order->credit > 0){
                            array_push($ArrOrderTrxCredit,$Order->credit);
                        }
                    }
                }
                $ArrResult[$i]['Status']                    = $item_status;
                $ArrResult[$i]['Debit']                     = array_sum($ArrOrderTrxDebit);
                $ArrResult[$i]['Credit']                    = array_sum($ArrOrderTrxCredit);
            }
            ### ORDER ###

            ### ORDER ITEMS ###
            if($item_status['id'] == 3){
                $OrderTrx                               = CashBookModel::where('status','=',3)
                    ->where('flag','=',0)
                    ->where('branch_id','=',$BranchID)
                    ->where('date_transaction','>=',$dateStart)
                    ->where('date_transaction','<=',$dateEnd)
                    ->get();
                if($OrderTrx){
                    $ArrOrderItemTrxDebit               = array();
                    $ArrOrderItemTrxCredit              = array();
                    foreach ($OrderTrx as $Order){
                        if($Order->debit > 0 && $Order->credit == 0){
                            array_push($ArrOrderItemTrxDebit,$Order->debit);
                        }else if($Order->debit == 0 && $Order->credit > 0){
                            array_push($ArrOrderItemTrxCredit,$Order->credit);
                        }
                    }
                }
                $ArrResult[$i]['Status']                    = $item_status;
                $ArrResult[$i]['Debit']                     = array_sum($ArrOrderItemTrxDebit);
                $ArrResult[$i]['Credit']                    = array_sum($ArrOrderItemTrxCredit);
            }
            ### ORDER ITEMS ###


            ### INCOME ###
            if($item_status['id'] == 4){
                $OrderTrx                               = CashBookModel::where('status','=',4)
                    ->where('flag','=',0)
                    ->where('branch_id','=',$BranchID)
                    ->where('date_transaction','>=',$dateStart)
                    ->where('date_transaction','<=',$dateEnd)
                    ->get();
                if($OrderTrx){
                    $ArrOrderIncomeTrxDebit             = array();
                    $ArrOrderIncomeTrxCredit            = array();
                    foreach ($OrderTrx as $Order){
                        if($Order->debit > 0 && $Order->credit == 0){
                            array_push($ArrOrderIncomeTrxDebit,$Order->debit);
                        }else if($Order->debit == 0 && $Order->credit > 0){
                            array_push($ArrOrderIncomeTrxCredit,$Order->credit);
                        }
                    }
                }
                $ArrResult[$i]['Status']                    = $item_status;
                $ArrResult[$i]['Debit']                     = array_sum($ArrOrderIncomeTrxDebit);
                $ArrResult[$i]['Credit']                    = array_sum($ArrOrderIncomeTrxCredit);
            }
            ### INCOME ###

            ### EXPENSE ###
            if($item_status['id'] == 5){
                $OrderTrx                               = CashBookModel::where('status','=',5)
                    ->where('flag','=',0)
                    ->where('branch_id','=',$BranchID)
                    ->where('date_transaction','>=',$dateStart)
                    ->where('date_transaction','<=',$dateEnd)
                    ->get();
                if($OrderTrx){
                    $ArrOrderIncomeTrxDebit             = array();
                    $ArrOrderIncomeTrxCredit            = array();
                    foreach ($OrderTrx as $Order){
                        if($Order->debit > 0 && $Order->credit == 0){
                            array_push($ArrOrderIncomeTrxDebit,$Order->debit);
                        }else if($Order->debit == 0 && $Order->credit > 0){
                            array_push($ArrOrderIncomeTrxCredit,$Order->credit);
                        }
                    }
                }
                $ArrResult[$i]['Status']                    = $item_status;
                $ArrResult[$i]['Debit']                     = array_sum($ArrOrderIncomeTrxDebit);
                $ArrResult[$i]['Credit']                    = array_sum($ArrOrderIncomeTrxCredit);
            }
            ### EXPENSE ###
            $i++;
        }

        ### EXCEL ###
        if($Action == 'excel') {
            $data = array(
                array('Pendapatan/Pengeluaran', 'Debit','Credit','Mutasi')
            );

            $Info           = array();
            $i              = 0;
            $Total                          = 0;
            foreach($ArrResult as $item){
                $Total                          = $Total + $item['Debit'] - $item['Credit'];
                $Info[$i][0]                    = $item['Status']['description'];
                $Info[$i][1]                    = $item['Debit'];
                $Info[$i][2]                    = $item['Credit'];
                $Info[$i][3]                    = $Total;
                $i++;
            }
                $Info[$i][0]                    = "TOTAL";
                $Info[$i][1]                    = "";
                $Info[$i][2]                    = "";
                $Info[$i][3]                    = $Total;

            Excel::create('Profit Loss'.DateFormat($dateStart,'F'), function($excel) use($Info,$data) {

                $excel->sheet('Report Profit Loss', function($sheet) use($Info,$data) {


                    $sheet->setOrientation('landscape');
                    $sheet->mergeCells('A1:E1');
                    $sheet->cell('A1', function($cell) {
                        $cell->setValue('Report Profit Loss per-'.date("d F Y"));
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '16',
                            'bold'       =>  true
                        ));
                        $cell->setAlignment('center');
                    });



                    // // HEADER //
                    $sheet->cell('B4', function($cell) {
                        $cell->setValue('Pendapatan / Pengeluaran');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('C4', function($cell) {
                        $cell->setValue('Debit');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('D4', function($cell) {
                        $cell->setValue('Credit');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('E4', function($cell) {
                        $cell->setValue('Mutasi');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });

                    // // HEADER //
                    // $sheet->fromArray($Info);

                    // RESULT //
                    $sheet->fromArray($Info, null, 'B5', false,false);
                    // RESULT //
                });
            })->export('xls');

        }else if($Action == 'pdf'){
            $data['ArrResult']                              = $ArrResult;
            $data['Branch']                                 = $BranchInfo->name;
            $data['Users']                                  = $Users;

            $pdf                                            = PDF::loadView('pdf.profitloss', $data);
            return $pdf->download('profitloss'.date('dmYHis').'.pdf');
        }

        $this->_data['ArrResult']                           = $ArrResult;
        $this->_data['Month']                               = $Month;
        $this->_data['Year']                                = $Year;
        $this->_data['BranchID']                            = $BranchID;

        return Theme::view('modules.report.profitloss.show',$this->_data);
    }

}
