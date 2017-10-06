<?php

namespace App\Modules\Order\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;
use App\Modules\Customer\Models\CustomerModel;
use App\Modules\Branch\Models\BranchModel;
use Auth;
use Theme;
use App\Modules\User\Models\UserModel;
use App\Modules\Order\Models\OrderModel;
use App\Modules\Order\Models\OrderDetailModel;
use App\Modules\Order\Models\OrderImageModel;
use App\Modules\Merk\Models\MerkModel;
use App\Modules\Treatmentpackage\Models\TreatmentPackageModel;

use App\Modules\Role\Models\Role;
use App\Modules\Cashbook\Models\CashBookModel;

use App\Jobs\SendMail;
use Illuminate\Auth\Events\Registered;
use App\User;

use \Milon\Barcode\DNS1D;

class OrderViewController extends Controller
{

    protected $_data = array();
    protected $destinationPath = array();

    public function __construct()
    {
        $this->destinationPath = public_path('images/item');

        $this->_data['string_menuname']             = 'Order';
        $this->_data['IDMENU']                      = 'Order';
    }

    public function details($id){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddOrder';

        $Users                                      = Auth::user();
        $Order                                      = OrderModel::find($id);
        $this->_data['order_id']                    = $id;

        $this->_data['Order']                       = $Order;
        $NominalDiscount                            = $Order->total * $Order->discount /100;
        $this->_data['Subtotal']                    = $Order->total - $NominalDiscount + $Order->additional + $Order->shipping_costs;
        $this->_data['Type']                        = get_Typepayment(1);

        $OrderDetail                                = OrderDetailModel::where('order_id','=',$id)->get();
        $this->_data['OrderDetail']                 = $OrderDetail;

        $OrderImage                                 = OrderImageModel::where('order_id','=',$id)->get();
        $this->_data['OrderImage']                  = $OrderImage;


        return Theme::view('modules.order.details',$this->_data);
    }
}
