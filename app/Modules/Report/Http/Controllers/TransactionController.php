<?php

namespace App\Modules\Report\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;

use App\Modules\Role\Models\Role;
use App\Modules\User\Models\UserModel;
use App\Modules\Customer\Models\CustomerModel;
use App\Modules\Branch\Models\BranchModel;
use App\Modules\Order\Models\OrderModel;
use App\Modules\Order\Models\OrderDetailModel;
use App\Modules\Order\Models\OrderImageModel;
use App\User;
use App\Modules\Cashbook\Models\CashBookModel;

use App\Jobs\SendMail;
use Illuminate\Auth\Events\Registered;

use Auth;
use Theme;
use Excel;
use PDF;


class TransactionController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:report-menu']);
        $this->middleware(['permission:report-transaction-view']);

        $this->_data['string_menuname']             = 'Transaction';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'Report';
        $this->_data['Users']                       = Auth::user();

        return Theme::view('modules.report.transaction.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'Report';
        $this->_data['IDSUBMENU']                   = 'ReportTransaction';
        $this->_data['Users']                       = Auth::user();

        return Theme::view('modules.report.transaction.show',$this->_data);
    }

    public function retrieve($get){
        $Get                                        = explode(" | ",base64_decode($get));
        $BranchID                                   = $Get[0];
        $From                                       = DateFormat($Get[1],"Y-m-d 00:00:00");
        $To                                         = DateFormat($Get[2],"Y-m-d 23:59:59");
        $Action                                     = $Get[3];

        $Users                                      = Auth::user();

        $Where                                      = array();
        if(bool_CheckAccessUser('access-pusat')){
            $Where['branch_id']                         = $BranchID;
        }else{
            $Where['branch_id']                         = $Users->branch_id;
        }

        $CashbBookInfo                              = CashBookModel::where($Where)
                                                                ->where('date_transaction',">=",$From)
                                                                ->where('date_transaction',"<=",$To)
                                                                ->get();

        $SumDebit                                   = CashBookModel::where($Where)
                                                                ->where('date_transaction',">=",$From)
                                                                ->where('date_transaction',"<=",$To)
                                                                ->sum('debit');

        $SumCredit                                  = CashBookModel::where($Where)
                                                                ->where('date_transaction',">=",$From)
                                                                ->where('date_transaction',"<=",$To)
                                                                ->sum('credit');

        $this->_data['ResultOrder']                 = $CashbBookInfo;
        $this->_data['SumDebit']                    = $SumDebit;
        $this->_data['SumCredit']                   = $SumCredit;
        $this->_data['BranchID']                    = $BranchID;
        $this->_data['Users']                       = Auth::user();
        $this->_data['from']                        = DateFormat($From,"d-m-Y");
        $this->_data['to']                          = DateFormat($To,"d-m-Y");
        $this->_data['state']                       = 'WithParam';


        ### CHART TRANSACTION ###
        $DateStart                                      = DateFormat($From,"Y-m-d");
        $DateEnd                                        = DateFormat($To,"Y-m-d");


        $DataChart                                      = array();
        $i                                              = 0;
        while (strtotime($DateStart) <= strtotime($DateEnd)) {
            $DateChartFrom                              = dateFormat($DateStart,"Y-m-d 00:00:00");
            $DateChartTo                                = dateFormat($DateStart,"Y-m-d 23:59:59");

            $TransactionDebitInBranchChart              = CashBookModel::where($Where)
                                                                            ->where('date_transaction','>=',$DateChartFrom)
                                                                            ->where('date_transaction','<=',$DateChartTo)
                                                                            ->sum('debit');

            $DataChart[$i]["date"]                      = dateFormat($DateStart,"d F");
            $DataChart[$i]["income"]                    = $TransactionDebitInBranchChart;
            $DateStart = date ("Y-m-d", strtotime("+1 day", strtotime($DateStart)));
            $i++;
        }

        $date1                                          = date_create(DateFormat($From,"Y-m-d"));
        $date2                                          = date_create(DateFormat($To,"Y-m-d"));
        $diff                                           = date_diff($date1,$date2);

        if($date1 == $date2 ){
            $this->_data['ChartInfo']                        = 1;
        }else{
            $this->_data['ChartInfo']                        = $diff->format("%r%a");
        }
        
        $this->_data['DataChart']                        = $DataChart;
        ### CHART TRANSACTION ###


        ### EXCEL ###
        if($Action == 'excel'){
            $data = array(
                array('No. Bukti', 'description','Tanggal','Debit','Credit')
            );

            $Info           = array();
            $i              = 0;
            foreach($CashbBookInfo as $item){
                $Info[$i][0]                    = $item->notransaction;
                $Info[$i][1]                    = $item->description;
                $Info[$i][2]                    = DateFormat($item->date_transaction,"d-m-Y H:i:s");
                $Info[$i][3]                    = number_format($item->debit,0,",",".");
                $Info[$i][4]                    = number_format($item->credit,0,",",".");
                $i++;
            }

            Excel::create('Transaction'.DateFormat($From,'dmy')."_".DateFormat($To,"dmy"), function($excel) use($Info,$data) {

                $excel->sheet('Report Transaction', function($sheet) use($Info,$data) {


                    $sheet->setOrientation('landscape');
                    $sheet->mergeCells('A1:G1');
                    $sheet->cell('A1', function($cell) {
                        $cell->setValue('Report Transaction per-'.date("d F Y"));
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '16',
                            'bold'       =>  true
                        ));
                        $cell->setAlignment('center');
                    });



                    // // HEADER //
                    $sheet->cell('B4', function($cell) {
                        $cell->setValue('No. Bukti');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('C4', function($cell) {
                        $cell->setValue('Description');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('D4', function($cell) {
                        $cell->setValue('Tanggal');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('E4', function($cell) {
                        $cell->setValue('Debit');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('F4', function($cell) {
                        $cell->setValue('Credit');
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
            $data['ResultOrder']            = $CashbBookInfo;
            $data['SumDebit']               = $SumDebit;
            $data['SumCredit']              = $SumCredit;
            $data['Branch']                 = $Users->branch->name;
            $data['Users']                  = $Users;

            $pdf = PDF::loadView('pdf.transaction', $data);
            return $pdf->download('transaction'.date('dmYHis').'.pdf');
        }

        return Theme::view('modules.report.transaction.show',$this->_data);

    }

}
