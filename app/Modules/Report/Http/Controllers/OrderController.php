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
        $BranchInfo                                 = BranchModel::find($Branch);
        $Percentage                                 = $BranchInfo->persentage;

        $ArrHarga                                   = array();
        $ArrKomisi                                  = array();
        $ArrTotal                                   = array();
        foreach($OrderInfo as $Info){
            $DetailItem                             = get_OrderDetailbyID($Info->id);
            foreach($DetailItem as $item){

                $Komisi                             = $BranchInfo->persentage * $item->total/100;
                $Tagihan                            = $item->total - $Komisi;
                array_push($ArrKomisi,$Komisi);
                array_push($ArrHarga,$item->total);
                array_push($ArrTotal,$Tagihan);
            }
        }

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
                array('No.','Tanggal', 'Customer','Code','Treatment','Kategori','Status')
            );

            $Info           = array();
            $i              = 0;
            $ArrTotal       = array();
            $x              = 1;
            foreach($OrderInfo as $item){
                $OrderDetail                    = OrderDetailModel::where('order_id',$item->id)->get();
                foreach($OrderDetail as $detail){
                    if($detail->treatment_category_id){
                        $Kategori                = $detail->treatmentcategory->name;
                    }else{
                        $Kategori                = "-";
                    }
                $Komisi                         = $detail->total * $Percentage/100;
                $Info[$i][0]                    = $x; //No.
                $Info[$i][1]                    = DateFormat($item->date_transaction,"d-m-Y"); //Tanggal
                $Info[$i][2]                    = $item->customer->name; //Customer
                $Info[$i][3]                    = $item->ref_number; // Code
                $Info[$i][4]                    = $detail->treatmentpackage->name; // Treatment
                $Info[$i][5]                    = $Kategori; //Kategori
                $Info[$i][6]                    = $detail->merk_name; //Brand
                $Info[$i][7]                    = $detail->total; //Harga
                $Info[$i][8]                    = $Komisi; //Komisi
                $Info[$i][9]                    = $detail->total - $Komisi; //Tagihan
                $i++;
                $x++;
                }
            }
            $BranchInfo                             = BranchModel::find($Branch);
            $Total                                  = array_sum($ArrTotal);
            $Info[$i][0]                            = "";
            $Info[$i][1]                            = "";
            $Info[$i][2]                            = "";
            $Info[$i][3]                            = "";
            $Info[$i][4]                            = "";
            $Info[$i][5]                            = "";
            $Info[$i][6]                            = "Total";
            $Info[$i][7]                            = array_sum($ArrHarga);
            $Info[$i][8]                            = array_sum($ArrKomisi);
            $Info[$i][9]                            = array_sum($ArrTotal);
            $i = $i + $i;
            $Info[$i][0]                            = "";
            $Info[$i][1]                            = "";
            $Info[$i][2]                            = "";
            $Info[$i][3]                            = "";
            $Info[$i][4]                            = "";
            $Info[$i][5]                            = "";
            $Info[$i][6]                            = "";
            $Info[$i][7]                            = "";
            $Info[$i][8]                            = $BranchInfo->city.", ".date('d F Y');
            $Info[$i][9]                            = "";

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
                        $cell->setValue('No.');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('C4', function($cell) {
                        $cell->setValue('Tanggal');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('D4', function($cell) {
                        $cell->setValue('Customer');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('E4', function($cell) {
                        $cell->setValue('Code');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('F4', function($cell) {
                        $cell->setValue('Treatment');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('G4', function($cell) {
                        $cell->setValue('Kategori');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('H4', function($cell) {
                        $cell->setValue('Brand');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('I4', function($cell) {
                        $cell->setValue('Harga');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });

                    $sheet->cell('J4', function($cell) {
                        $cell->setValue('Komisi');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });

                    $sheet->cell('K4', function($cell) {
                        $cell->setValue('Tagihan');
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
                        $sheet->cell('i'.$i, $d[7]);
                        $sheet->cell('j'.$i, $d[8]);
                        $sheet->cell('k'.$i, $d[9]);
                        $i++;
                    }

                    $i = $i + 3;
                    $sheet->cell('J'.$i, "(".$BranchData['name'].")");
                    // RESULT //
//                    $sheet->fromArray($Info, null, 'B5', false,false);
                    // RESULT //
                });
            })->export('xls');
        }else if($Action == 'pdf'){
            $dateNow                        = date('d F Y');
            $date                           = strtotime($dateNow);
            $date                           = strtotime("+9 day", $date);
            $DateExpirate                   = date("d F Y",$date);

            $data['ResultOrder']            = $OrderInfo;
            $data['SumHarga']               = array_sum($ArrHarga);
            $data['SumKomisi']              = array_sum($ArrKomisi);
            $data['SumTotal']               = array_sum($ArrTotal);
            $data['Branch']                 = $BranchInfo;
            $data['Users']                  = $Users;
            $data['persentage']             = $BranchInfo->persentage;
            $data['Tanggal']                = $dateNow;
            $data['JatuhTempo']             = $DateExpirate;


            $pdf = PDF::loadView('pdf.order', $data)->setPaper('a4', 'landscape');
            return $pdf->download('order.pdf');
        }

        return Theme::view('modules.report.order.show',$this->_data);

    }

}
