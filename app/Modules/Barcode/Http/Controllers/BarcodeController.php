<?php

namespace App\Modules\Barcode\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Barcode\Models\Barcode as BarcodeModel;
use App\Modules\Barcode\Models\BarcodeList as BarcodeListModel;

use Auth;
use Theme;
use Activity;
use PDF;

use \Milon\Barcode\DNS1D;


class BarcodeController extends Controller
{

    protected $_data = array();
    protected $destinationPDF = array();
    public function __construct()
    {
        $this->middleware(['permission:barcode-generate']);
        $this->pathPDF                              = 'media/barcode/';
        $this->destinationPDF                       = public_path($this->pathPDF);

        $this->_data['string_menuname']             = 'Barcode';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'barcode';


        return Theme::view('modules.barcode.show',$this->_data);
    }

    public function show(){
        $data['Barcode']                         = "Barcode";

        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'barcode';


        return Theme::view('modules.barcode.show',$this->_data);
    }

    public function datatables(){
        $Barcode = BarcodeModel::select(['id', 'count', 'created_at', 'created_by']);

        return Datatables::of($Barcode)
            ->addColumn('href', function ($Barcode) {
                return '<a href="'.route('barcode_download',$Barcode->id).'" target="_blank" class="btn btn-info"><i class="fa fa-download"></i></a>&nbsp;
                        ';
            })

            ->editColumn('created_by', function ($Barcode) {
                    return get_NameUser($Barcode->created_by);
            })

            ->editColumn('created_at', function ($Barcode) {
                return DateFormat($Barcode->created_at,"d/m/Y H:i:s");
            })

            ->rawColumns(['href','address','is_active'])
            ->make(true);
    }

    public function generate(Request $request){

        $Barcode                                    = new BarcodeModel();
        $Barcode->count                             = $request->count;
        $Barcode->created_by                        = Auth::id();
        $Barcode->updated_by                        = Auth::id();
        if($Barcode->save()){
            for($x=1;$x<=$request->count;$x++){
                $BarcodeList                            = new BarcodeListModel();
                $BarcodeList->barcode_id                = $Barcode->id;
                $BarcodeList->created_by                = Auth::id();
                $BarcodeList->updated_by                = Auth::id();
                $BarcodeList->save();

                $d                                          = new DNS1D();
                $d->setStorPath(__DIR__."/cache/");

                $Format                                 = "62".date('ymd').sprintf("%04s",$BarcodeList->id);
                $Format                                 = ean13_check_digit($Format);
                $arrBarcode[$x]['Barcode']              = $d->getBarcodeHTML($Format, "EAN13");
                $arrBarcode[$x]['Format']               = $Format;
                $BarcodeUpdate                          = BarcodeListModel::find($BarcodeList->id);
                $BarcodeUpdate->barcode                 = $Format;
                $BarcodeUpdate->save();
            }
            try {
                $data['ArrBarcode']                     =  $arrBarcode;
                $File                                   = "Barcode-".$Barcode->id.'.pdf';
                $pdf                                    =  PDF::loadView('pdf.barcode_generator', $data);
                $pdf->save($this->destinationPDF.$File);

                $BarcodeUpdate                          = BarcodeModel::find($Barcode->id);
                $BarcodeUpdate->file                    = $File;
                $BarcodeUpdate->save();
                $data                                    = array(
                    "status"                                => true,
                    "message"                               => 'Generate barcode Berhasil. Silakan download di daftar anda.',
                    "output"                                => array(
                        "barcode"                     => $arrBarcode
                    )
                );
            }catch (\Exception $e) {
                Activity::log([
                    'contentId'   => $Barcode->id,
                    'contentType' => 'Barcode Generate',
                    'action'      => 'generate',
                    'description' => $e->getMessage(),
                    'details'     => json_encode($arrBarcode),
                    'updated'     => Auth::id(),
                ]);

                $data                                    = array(
                    "status"                                => false,
                    "message"                               => 'Maaf, ada kesalahan teknis. Mohon hubungi web administrator(Error generate)',
                    "output"                                => array(
                        "barcode_count"                     => $request->count
                    )
                );
            }
        }

        return response($data, 200)
        ->header('Content-Type', 'text/plain');
    }

    public function download($id){
        $Barcode                                        = BarcodeModel::find($id);
        return redirect(url('/'.$this->pathPDF.$Barcode->file));
//        return $this->destinationPDF.$Barcode->file;

    }

}
