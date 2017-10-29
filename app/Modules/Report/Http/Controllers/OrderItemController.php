<?php

namespace App\Modules\Report\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Modules\Role\Models\Role;
use App\Modules\User\Models\UserModel;
use App\Modules\Customer\Models\CustomerModel;
use App\Modules\Branch\Models\BranchModel;
use App\Modules\Order\Models\OrderItemModel;
use App\Modules\Order\Models\OrderItemDetailModel;
use App\User;

use App\Jobs\SendMail;
use Illuminate\Auth\Events\Registered;

use Auth;
use Theme;

use Activity;
use Excel;
use PDF;

class OrderItemController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:report-menu']);
        $this->middleware(['permission:report-orderitem-view']);

        $this->_data['string_menuname']             = 'Report';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'Order Item';

        return Theme::view('modules.report.order_item.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'Order Item';

        return Theme::view('modules.report.order_item.show',$this->_data);
    }

    public function retrieve($get){
        $Get                                        = explode(" | ",base64_decode($get));
        $BranchID                                   = $Get[0];
        $SupplierID                                 = $Get[1];
        $From                                       = DateFormat($Get[2],"Y-m-d 00:00:00");
        $To                                         = DateFormat($Get[3],"Y-m-d 23:59:59");
        $Action                                     = $Get[4];

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

        if($SupplierID > 0){
            $Where['supplier_id']                       = $SupplierID;
        }

        $OrderInfo                                  = OrderItemModel::where($Where)
                                                                ->where('date_transaction',">=",$From)
                                                                ->where('date_transaction',"<=",$To)
                                                                ->where('status',"=",1)
                                                                ->get();

        $Total                                      = OrderItemModel::where($Where)
                                                                ->where('date_transaction',">=",$From)
                                                                ->where('date_transaction',"<=",$To)
                                                                ->where('status',"=",1)
                                                                ->sum('payment');
        $BranchInfo                                 = BranchModel::find($Branch);
        $Percentage                                 = $BranchInfo->persentage;

        $this->_data['ResultOrder']                 = $OrderInfo;
        $this->_data['BranchID']                    = $BranchID;
        $this->_data['SupplierID']                  = $SupplierID;
        $this->_data['Total']                       = $Total;

        $this->_data['from']                        = DateFormat($From,"d-m-Y");
        $this->_data['to']                          = DateFormat($To,"d-m-Y");
        $this->_data['state']                       = 'WithParam';

        ### EXCEL ###
        if($Action == 'excel'){
            $data = array(
                array('No.','Code','Tanggal', 'Produk','Harga','Jumlah','Discount','Additional','Total')
            );

            $Info           = array();
            $i              = 0;
            $ArrTotal       = array();
            $x              = 1;
            foreach($OrderInfo as $item){
                foreach($item->order_item_detail as $detail){

                $Info[$i][0]                    = $x; //No.
                $Info[$i][1]                    = $item->ref_number; // Code
                $Info[$i][2]                    = DateFormat($item->date_transaction,"d-m-Y"); //Tanggal
                $Info[$i][3]                    = $detail->stock->name; //Stock
                $Info[$i][4]                    = $detail->price; // PRICE
                $Info[$i][5]                    = $detail->quantity; //QTY
                $Info[$i][6]                    = $detail->price - ($detail->price * $detail->discount/100); //Discount
                $Info[$i][7]                    = $detail->additional; //ADDITIONAL
                $Info[$i][8]                    = $detail->total; //TOTAL
                $i++;
                $x++;
                }
            }
            $BranchInfo                             = BranchModel::find($Branch);
            $Info[$i][0]                            = "";
            $Info[$i][1]                            = "";
            $Info[$i][2]                            = "";
            $Info[$i][3]                            = "";
            $Info[$i][4]                            = "";
            $Info[$i][5]                            = "";
            $Info[$i][6]                            = "";
            $Info[$i][7]                            = "Total";
            $Info[$i][8]                            = $Total;
            $i = $i + $i;
            $Info[$i][0]                            = "";
            $Info[$i][1]                            = "";
            $Info[$i][2]                            = "";
            $Info[$i][3]                            = "";
            $Info[$i][4]                            = "";
            $Info[$i][5]                            = "";
            $Info[$i][6]                            = "";
            $Info[$i][7]                            = $BranchInfo->city.", ".date('d F Y');
            $Info[$i][8]                            = "";

            $BranchData                         = array(
                "name"                          => $BranchInfo->name,
                "address"                       => $BranchInfo->address,
                "phone"                         => $BranchInfo->phone
            );

            Excel::create('OrderItem'.DateFormat($From,'dmy')."_".DateFormat($To,"dmy"), function($excel) use($Info,$data,$BranchData) {

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
                        $cell->setValue('Code');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('D4', function($cell) {
                        $cell->setValue('Tanggal Transaksi');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('E4', function($cell) {
                        $cell->setValue('Produk');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('F4', function($cell) {
                        $cell->setValue('Harga');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('G4', function($cell) {
                        $cell->setValue('Jumlah');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('H4', function($cell) {
                        $cell->setValue('Discount');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });
                    $sheet->cell('I4', function($cell) {
                        $cell->setValue('Additional');
                        $cell->setBackground('#36c6d3');
                        $cell->setFont(array(
                            'family'     => 'Calibri',
                            'size'       => '12',
                            'bold'       =>  true
                        ));
                    });

                    $sheet->cell('J4', function($cell) {
                        $cell->setValue('Total');
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
            $data['Branch']                 = $BranchInfo;
            $data['Users']                  = $Users;
            $data['persentage']             = $BranchInfo->persentage;
            $data['Tanggal']                = $dateNow;
            $data['JatuhTempo']             = $DateExpirate;


            $pdf = PDF::loadView('pdf.order_item', $data)->setPaper('a4', 'landscape');
            return $pdf->download('order_item.pdf');
        }

        return Theme::view('modules.report.order_item.show',$this->_data);

    }


}
