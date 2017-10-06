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

use App\Jobs\SendMail;
use Illuminate\Auth\Events\Registered;

use Auth;
use Theme;

use Activity;
use Excel;
use PDF;

class OrderController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:report-menu']);
        $this->middleware(['permission:report-order-view']);

        $this->_data['string_menuname']             = 'Report';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'Order';

        return Theme::view('modules.report.order.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'Order';
        $this->_data['IDSUBMENU']                   = 'OrderTransaction';

        return Theme::view('modules.report.order.show',$this->_data);
    }

    public function retrieve($get){
        $Get                                        = explode(" | ",base64_decode($get));
        $BranchID                                   = $Get[0];
        $CustomerID                                 = $Get[1];
        $From                                       = DateFormat($Get[2],"Y-m-d 00:00:00");
        $To                                         = DateFormat($Get[3],"Y-m-d 23:59:59");
        $Status                                     = $Get[4];
        $Action                                     = $Get[5];

        $Users                                      = Auth::user();
        $Where                                      = array();


        if(bool_CheckAccessUser('access-pusat')){
            if($BranchID > 0){
                $Where['branch_id']                         = $BranchID;
                $Branch                                     = $BranchID;
            }else{
                $Where['branch_id']                         = $Users->branch_id;
                $Branch                                     = $Users->branch_id;
            }
        }else{
            $Where['branch_id']                         = $Users->branch_id;
            $Branch                                     = $Users->branch_id;
        }

        if($CustomerID > 0){
            $Where['customer_id']                   = $CustomerID;
        }

        if($Status > 0){
            $Where['status']                        = $Status;
        }

        $OrderInfo                                  = OrderModel::where($Where)
                                                                ->where('date_transaction',">=",$From)
                                                                ->where('date_transaction',"<=",$To)
                                                                ->where('invoice',">",0)
                                                                ->get();

        $SumDownPayment                             = OrderModel::where($Where)
                                                                ->where('date_transaction',">=",$From)
                                                                ->where('date_transaction',"<=",$To)
                                                                ->where('invoice',">",0)
                                                                ->sum('down_payment');

        $SumFullPayment                             = OrderModel::where($Where)
                                                                ->where('date_transaction',">=",$From)
                                                                ->where('date_transaction',"<=",$To)
                                                                ->where('invoice',">",0)
                                                                ->sum('full_payment');


        $SumTotal                                   = OrderModel::where($Where)
                                                                ->where('date_transaction',">=",$From)
                                                                ->where('date_transaction',"<=",$To)
                                                                ->where('invoice',">",0)
                                                                ->sum('total');


        $this->_data['ResultOrder']                 = $OrderInfo;
        $this->_data['BranchID']                    = $BranchID;
        $this->_data['CustomerID']                  = $CustomerID;
        $this->_data['from']                        = DateFormat($From,"d-m-Y");
        $this->_data['to']                          = DateFormat($To,"d-m-Y");
        $this->_data['Status']                      = $Status;
        $this->_data['state']                       = 'WithParam';

        ### EXCEL ###
        if($Action == 'excel'){
            $data = array(
                array('Code', 'Customer','Tanggal','Tagihan','Payment','Status')
            );

            $Info           = array();
            $i              = 0;
            $ArrTotal       = array();
            foreach($OrderInfo as $item){
                $Info[$i][0]                    = $item->ref_number;
                $Info[$i][1]                    = $item->customer->name;
                $Info[$i][2]                    = DateFormat($item->date_transaction,"d-m-Y H:i:s");
                $Info[$i][3]                    = get_statusorder($item->status);
                $Info[$i][4]                    = number_format($item->down_payment + $item->full_payment);
                $Info[$i][5]                    = number_format($item->total);
                $Info[$i][6]                    = "";
                array_push($ArrTotal,$item->total);
                $i++;
                $j = 1;
                $OrderDetail                    = OrderDetailModel::where('order_id',$item->id)->get();
                foreach($OrderDetail as $detail){
                    $Info[$i][0]                    = $j.".";
                    $Info[$i][1]                    = $detail->treatment->name;
                    if($detail->treatment_category_id){
                        $Info[$i][2]                = $detail->treatmentcategory->name;
                    }else{
                        $Info[$i][2]                = "-";
                    }
                    $Info[$i][3]                    = $detail->treatmentpackage->name;
                    $Info[$i][4]                    = $detail->merk_name;
                    $Info[$i][5]                    = number_format($detail->total);
                    $Info[$i][6]                    = $detail->additional_description;
                    $j++;
                    $i++;
                }
                $i++;
            }
            $BranchInfo                         = BranchModel::find($Branch);
            $Total                                  = array_sum($ArrTotal);
            $Info[$i][0]                            = "";
            $Info[$i][1]                            = "";
            $Info[$i][2]                            = "";
            $Info[$i][3]                            = "";
            $Info[$i][4]                            = "Total";
            $Info[$i][5]                            = number_format($Total);
            $Info[$i][6]                            = "";
            $i = $i + $i;
            $Info[$i][0]                            = "";
            $Info[$i][1]                            = "";
            $Info[$i][2]                            = "";
            $Info[$i][3]                            = "";
            $Info[$i][4]                            = "";
            $Info[$i][5]                            = $BranchInfo->city.", ".date('d F Y');
            $Info[$i][6]                            = "";

            $BranchData                         = array(
                "name"                          => $BranchInfo->name,
                "address"                       => $BranchInfo->address,
                "phone"                         => $BranchInfo->phone
            );


            Excel::create('Order'.DateFormat($From,'dmy')."_".DateFormat($To,"dmy"), function($excel) use($Info,$data,$BranchData) {

                $excel->sheet('Report Order', function($sheet) use($Info,$data,$BranchData) {


                    $sheet->setOrientation('landscape');
                    $sheet->mergeCells('C2:E2');
                    // // HEADER //
                    $sheet->cell('C1', $BranchData['name']);
                    $sheet->cell('C2', $BranchData['address']);
                    $sheet->cell('C3', $BranchData['phone']);



                    // // HEADER //
                    $sheet->cell('B4', function($cell) {
                        $cell->setValue('Code');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('C4', function($cell) {
                        $cell->setValue('Customer');
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
                        $cell->setValue('Status');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('F4', function($cell) {
                        $cell->setValue('Payment');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('G4', function($cell) {
                        $cell->setValue('Tagihan');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('H4', function($cell) {
                        $cell->setValue('Additional Note');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    // // HEADER //
                    // $sheet->fromArray($Info);
                    $i = 5;
                    foreach($Info as $d){
                        $sheet->cell('B'.$i, $d[0]);
                        $sheet->cell('C'.$i, $d[1]);
                        $sheet->cell('D'.$i, $d[2]);
                        $sheet->cell('E'.$i, $d[3]);
                        $sheet->cell('F'.$i, $d[4]);
                        $sheet->cell('G'.$i, $d[5]);
                        $sheet->cell('H'.$i, $d[6]);
                        $i++;
                    }

                    $i = $i + 3;
                    $sheet->cell('G'.$i, "(".$BranchData['name'].")");
                    // RESULT //
//                    $sheet->fromArray($Info, null, 'B5', false,false);
                    // RESULT //
                });
            })->export('xls');
        }else if($Action == 'pdf'){
            $data['ResultOrder']            = $OrderInfo;
            $data['SumPayment']             = $SumDownPayment + $SumFullPayment;
            $data['SumTotal']               = $SumTotal;
            $data['Branch']                 = $Users->branch->name;
            $data['Users']                  = $Users;


            $pdf = PDF::loadView('pdf.order', $data);
            return $pdf->download('order.pdf');
        }

        return Theme::view('modules.report.order.show',$this->_data);

    }

}
