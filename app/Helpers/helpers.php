<?php
use \App\Modules\Users\Models\Users;

if (! function_exists('get_default_password')) {
    /**
     * @return string
     */
    function get_default_password()
    {
        return "Yourbagspa".date('d');
    }
}

if (! function_exists('get_UsersInformation')) {
    /**
     * @param string
     * @return string
     */
    function get_UsersInformation($field)
    {
        $arr_Users = Auth::user();
        if($arr_Users){
            return $arr_Users->$field;
        }
        return;
    }
}

if (! function_exists('bool_CheckUserRole')) {
    /**
     * @param string
     * @return boolean
     */
    function bool_CheckUserRole($string)
    {
        return Auth::user()->hasRole($string);
    }
}

if (! function_exists('bool_CheckAccessUser')) {
    /**
     * @param string
     * @return boolean
     */
    function bool_CheckAccessUser($string)
    {
        return Auth::user()->can($string);
    }
}



if (! function_exists('get_CustomerGroupDetail')) {
    /**
     * @param string
     * @return string
     */
    function get_CustomerGroupDetail($field)
    {
        $arr_Users      = Auth::user();
        $Group          = new App\Modules\Users\Models\Users_group();
        $Results        = $Group->get_CustomerGroupDetail($arr_Users->customer_group_id);

        if($Results){
            return $Results->$field;
        }else{
            return;
        }
    }
}


if (! function_exists('get_active_user')) {
    /**
     * @param integer
     * @return string
     */
    function get_active_user($integer)
    {
        if($integer == 1){
            return 'active';
        }else{
            return 'inactive';
        }
    }
}

if (! function_exists('createMenu')) {
    /**
     * @param integer
     * @return string
     */
    function createMenu()
    {
        $arr_Menu                   = App\Modules\Menu\Models\MenuModel::orderBy('name')->get();
        $arr_Send                   = array();
        if($arr_Menu){
            $i = 0;
            foreach($arr_Menu as $Menu){
                if(Illuminate\Support\Facades\Auth::user()->can($Menu->permission)){
                    $arr_Send[$i]['id']                 = $Menu->id;
                    $arr_Send[$i]['name']               = $Menu->name;
                    $arr_Send[$i]['url']                = $Menu->url;
                    $arr_Send[$i]['permission']         = $Menu->permission;
                    $arr_Send[$i]['icon']               = $Menu->icon;
                    $arr_Send[$i]['id_menu']            = str_replace(' ', '', $Menu->name );
                    if(App\Modules\Submenu\Models\SubmenuModel::where('parent_id', $Menu->id)->count() > 0){
                        $activeMenu                     = $Menu->url."/*";
                    }else{
                        $activeMenu                     = $Menu->url;
                    }
                    $arr_Send[$i]['active_menu']        = $activeMenu;
                    $arr_Submenu                        = App\Modules\Submenu\Models\SubmenuModel::where('parent_id', $Menu->id)->orderBy('name')->get();
                    if($arr_Submenu){
                        $y = 0;
                        foreach($arr_Submenu as $SubMenu){
                            if(Illuminate\Support\Facades\Auth::user()->can($SubMenu->permission)){
                                $arr_Send[$i]['arrSubMenu'][$y]['id_submenu']   = str_replace(' ', '', $Menu->name );
                                $arr_Send[$i]['arrSubMenu'][$y]['name']         = $SubMenu->name;
                                $arr_Send[$i]['arrSubMenu'][$y]['url']          = $SubMenu->url;
                                $arr_Send[$i]['arrSubMenu'][$y]['icon']         = $SubMenu->icon;
                                $arr_Send[$i]['arrSubMenu'][$y]['permission']   = $SubMenu->permission;
                                $arr_Send[$i]['arrSubMenu'][$y]['parent_id']    = $SubMenu->parent_id;
                                $y = $y+1;
                            }
                        }
                    }
                    $i = $i+1;
                }
            }
        }
        session()->put('Menu',$arr_Send);
    }
}



if (! function_exists('get_UserInformationByID')) {
    /**
     * @param integer
     * @return object
     */
    function get_UserInformationByID($id)
    {
        $obj_User = App\Modules\Users\Models\Users::find($id);
        if($obj_User){
            return $obj_User;
        }
        return;
    }
}

if (! function_exists('get_NameUser')) {
    /**
     * @param integer
     * @return object
     */
    function get_NameUser($id)
    {
        $obj_User = App\User::find($id);
        if($obj_User){
            return $obj_User->name;
        }
        return;
    }
}


if (! function_exists('get_DateNow')) {
    /**
     * @param string
     * @return string
     */
    function get_DateNow($Format)
    {
        return date($Format);
    }
}

if (! function_exists('set_json_encode')) {
    /**
     * @param string
     * @return string
     */
    function set_json_encode($Array)
    {
        return json_encode($Array);
    }
}

if (! function_exists('DateFormat')) {
    /**
     * @param string
     * @return string
     */
    function DateFormat($Date,$Format)
    {
        return date($Format,  strtotime($Date));
    }
}


if (! function_exists('bool_get_useragent_info')) {
    /**
     * @param String
     * @return boolean
     */
    function bool_get_useragent_info($String)
    {

        $Agent          = new \Jenssegers\Agent\Agent();
        return $Agent->$String();
    }
}

if (! function_exists('bool_checkAccessMember')) {
    /**
     * @param String
     * @return boolean
     */
    function bool_checkAccessMember($String)
    {
        $Permission         = \App\Modules\Permission\Models\Permission::Where('name','=',$String)->first();
        $PermissionID       = $Permission->id;

        $Where              = array(
            'user_id'       => \Illuminate\Support\Facades\Auth::id(),
            'permission_id' => $PermissionID
        );
        $PermissionMember   = \App\Modules\Permission\Models\PermissionMember::Where($Where)->count();
        if($PermissionMember > 0){
            return true;
        }
        return;
    }
}

if (! function_exists('get_CustomerGroupDetailbyID')) {
    /**
     * @param ,GroupID,string
     * @return string
     */
    function get_CustomerGroupDetailbyID($GroupID,$field)
    {
        $Group          = new App\Modules\Users\Models\Users_group();
        $Results        = $Group->get_CustomerGroupDetail($GroupID);

        if($Results){
            return $Results->$field;
        }else{
            return;
        }
    }
}

if (! function_exists('get_ListPermission')) {
    /**
     * @return string
     */
    function get_ListPermission()
    {
        $Permission                                 = App\Modules\Permission\Models\Permission::all();

        $output = array();
        $output [0] = 'Choose Permission';
        foreach ($Permission as $item){
            $output[$item->name] = $item->name;
        }
        return $output;

    }
}


if (! function_exists('get_ListTreatment')) {
    /**
     * @return string
     */
    function get_ListTreatment()
    {
        $Treatment                                  = App\Modules\Treatment\Models\TreatmentModel::where('is_active','=',1)->get();

        $output                                     = array();
        $output [0]                                 = 'Choose Treatment';
        foreach ($Treatment as $item){
            $output[$item->id]                    = $item->name;
        }
        return $output;

    }
}

if (! function_exists('get_ListTreatmentCategory')) {
    /**
     * @return string
     */
    function get_ListTreatmentCategory($Treatment="")
    {
        $Where                                      = array(
            'is_active'                             => 1,
            'treatment_id'                          => $Treatment
        );
        $TreatmentCategory                          = App\Modules\Treatmentcategory\Models\TreatmentCategoryModel::where($Where)->get();

        $output                                     = array();
        $output [0]                                 = 'Choose Treatment Category';
        foreach ($TreatmentCategory as $item){
            $output[$item->id]                    = $item->name;
        }
        return $output;

    }
}

if (! function_exists('get_active')) {
    /**
     * @param integer
     * @return string
     */
    function get_active($integer)
    {
        if($integer == 1){
            return 'active';
        }else{
            return 'inactive';
        }
    }
}

if (! function_exists('get_lunas')) {
    /**
     * @param integer
     * @return string
     */
    function get_lunas($integer)
    {
        if($integer == 0){
            return 'no';
        }else{
            return 'yes';
        }
    }
}

if (! function_exists('get_statusorder')) {
    /**
     * @param integer
     * @return string
     */
    function get_statusorder($integer)
    {

        if($integer == 0){
            return 'Proccess';
        }else{
            $StatusItem                                 = App\Status_item::find($integer);
            return $StatusItem->name;
        }
    }
}

if (! function_exists('get_categoryByID')) {
    /**
     * @param CategoryID,$field
     * @return string
     */
    function get_categoryByID($CategoryID,$field)
    {
        $Results          = App\Modules\Treatmentcategory\Models\TreatmentCategoryModel::find($CategoryID);

        if($Results){
            return $Results->$field;
        }else{
            return;
        }
    }
}


if (! function_exists('set_clearFormatRupiah')) {
    /**
     * @param $string
     * @return string
     */
    function set_clearFormatRupiah($string)
    {
        $Result = str_replace('.',"",$string);
        $Result = str_replace('_',"",$Result);
        $Result = str_replace('Rp ',"",$Result);

        return $Result;
    }
}

if (! function_exists('set_str_replace')) {
    /**
     * @param $string
     * @return string
     */
    function set_str_replace($string,$replace,$find)
    {
        $Result = str_replace($find,$replace,$string);

        return $Result;
    }
}


if (! function_exists('get_ListBranch')) {
    /**
     * @return string
     */
    function get_ListBranch()
    {
        $Branch                                     = App\Modules\Branch\Models\BranchModel::where('is_active','=',1)->get();

        $output                                     = array();
        $output [0]                                 = 'Pilih Cabang';
        foreach ($Branch as $item){
            $output[$item->id]                    = $item->name;
        }
        return $output;

    }
}

if (! function_exists('get_ListBranchActive')) {
    /**
     * @return string
     */
    function get_ListBranchActive()
    {
        $Branch                                     = App\Modules\Branch\Models\BranchModel::where('is_active','=',1)->get();

        if($Branch){
            return $Branch;
        }
        return;

    }
}

if (! function_exists('get_ListRole')) {
    /**
     * @return string
     */
    function get_ListRole()
    {
        $Role                                 = App\Modules\Role\Models\Role::all();

        $output = array();
        $output [0] = 'Choose Role';
        foreach ($Role as $item){
            if($item->name != 'super'){
                $output[$item->id] = $item->name;
            }
        }
        return $output;

    }
}


if (! function_exists('get_ListCustomer')) {
    /**
     * @return string
     */
    function get_ListCustomer()
    {
        $WhereCustomer                              = array(
            "status"                                => 1
        );
        $Customer                                     = App\Modules\Customer\Models\CustomerModel::where($WhereCustomer)->get();

        $output                                     = array();
        $output [0]                                 = 'Pilih Customer';
        foreach ($Customer as $item){
            $output[$item->id]                      = $item->name." [Branch ".$item->branch->name."]";
        }
        return $output;

    }
}

if (! function_exists('get_ListCustomerbyID')) {
    /**
     * @param integer
     * @return string
     */
    function get_ListCustomerbyID($branch_id)
    {
        $WhereCustomer                              = array(
            "status"                                => 1,
            "branch_id"                             => $branch_id
        );
        $Customer                                   = App\Modules\Customer\Models\CustomerModel::where($WhereCustomer)->get();

        $output                                     = array();
        $output [0]                                 = 'Pilih Customer';
        foreach ($Customer as $item){
            $output[$item->id]                      = $item->name." [Branch ".$item->branch->name."]";
        }
        return $output;

    }
}

if (! function_exists('get_ListEventbyID')) {
    /**
     * @param integer
     * @return string
     */
    function get_ListEventbyID($branch_id)
    {
        $WhereEvent                                 = array(
            "is_active"                                => 1,
            "branch_id"                             => $branch_id
        );
        $Event                                      = App\Modules\Event\Models\Event::where($WhereEvent)->get();

        $output                                     = array();
        $output [0]                                 = 'Pilih Event';
        foreach ($Event as $item){
            $output[$item->id]                      = $item->name;
        }
        return $output;

    }
}

if (! function_exists('get_ListMerk')) {
    /**
     * @return string
     */
    function get_ListMerk()
    {
        $Merk                                 = App\Modules\Merk\Models\MerkModel::all();

        $output = array();
        $output [0] = 'Choose Merk';
        foreach ($Merk as $item){
                $output[$item->id] = $item->name;
        }
        return $output;

    }
}

if (! function_exists('get_ListTreatment')) {
    /**
     * @return array
     */
    function get_ListTreatment()
    {
        $Treatment                              = App\Modules\Treatment\Models\TreatmentModel::all();

        $output = array();
        $output [0] = 'Choose Treatment';
        foreach ($Treatment as $item){
                $output[$item->id] = $item->name;
        }
        return $output;

    }
}

if (! function_exists('set_numberformat')) {
    /**
     * @return number
     */
    function set_numberformat($nominal)
    {
        return number_format($nominal,0,',',".");
    }
}

if (! function_exists('set_numberformatNotmal')) {
    /**
     * @return number
     */
    function set_numberformatNotmal($nominal)
    {
        return number_format($nominal);
    }
}

if (! function_exists('get_orderimage')) {
    /**
     * @return int
     */
    function get_orderimage($order_detail_id)
    {
        $OrderImage                                 = App\Modules\Order\Models\OrderImageModel::where('order_detail_id',$order_detail_id)
                                                                                                ->where('flag',1)
                                                                                                ->get();
        $Image                                      = '';
        foreach($OrderImage as $item){
            $Image                                  .= '<img src="'.url("/").'/images/item/'.$item->file.'" style="width:50px;height:50px;">';
        }
        return $Image;
    }
}

if (! function_exists('get_orderimagefinish')) {
    /**
     * @return int
     */
    function get_orderimagefinish($order_detail_id)
    {
        $OrderImage                                 = App\Modules\Order\Models\OrderImageModel::where('order_detail_id',$order_detail_id)
                                                                                                ->where('flag',2)
                                                                                                ->get();
        $Image                                      = '';
        foreach($OrderImage as $item){
            $Image                                  .= '<img src="'.url("/").'/images/item/'.$item->file.'" style="width:50px;height:50px;">';
        }
        return $Image;
    }
}

if (! function_exists('get_countorderimage')) {
    /**
     * @return int
     */
    function get_countorderimage($order_detail_id)
    {
        return  App\Modules\Order\Models\OrderImageModel::where('order_detail_id',$order_detail_id)->count();
    }
}

if (! function_exists('get_PaymentType')) {
    /**
     * @return array
     */
    function get_PaymentType()
    {
        $PaymentType                                    = App\Payment_type::where('is_active',1)->orderBy('name')->get();

        $output = array();
        $output [0] = 'Choose Payment Type';
        foreach ($PaymentType as $item){
                $output[$item->id] = $item->name;
        }
        return $output;

    }
}

if (! function_exists('get_Type')) {
    /**
     * @return array
     */
    function get_Type()
    {

        $output = array();
        $output [0] = 'Down Payment';
        $output [1] = 'Full Payment';
        return $output;
    }
}

if (! function_exists('get_ListChangedStatus')) {
    /**
     * @return array
     */
    function get_ListChangedStatus()
    {
        $StatusItem                                 = App\Status_item::all();

        $output                                     = array();
        $output [0]                                 = 'Pilih Status';
        foreach ($StatusItem as $item){
            $output[$item->id]                      = $item->name;
        }

        // $output = array();
        // $output [0] = 'Pilih Status';
        // $output [1] = 'Selesai';
        // $output [2] = 'Sudah diambil';
        return $output;
    }
}


if (! function_exists('get_Typepayment')) {
    /**
     * @param integer
     * @return string
     */
    function get_Typepayment($integer)
    {
        if($integer == 0){
            return 'Down Payment';
        }else{
            return 'Full Payment';
        }
    }
}

if (! function_exists('set_PaymentType')) {
    /**
     * @param integer
     * @return string
     */
    function set_PaymentType($integer)
    {
        $PaymentType                                    = App\Payment_type::find($integer);
        if($PaymentType){
            return $PaymentType->name;
        }
        return "-";

    }
}


if (! function_exists('get_date_add')) {
    /**
     * @param integer
     * @return string
     */
    function get_date_add($Date,$interval)
    {
        $stop_date = new DateTime($Date);
        $stop_date->modify('+'.$interval.' day');
        return $stop_date->format('d F Y');
    }
}

if (! function_exists('get_CountOrderImageOne')) {
    /**
     * @param integer
     * @return string
     */
    function get_CountOrderImageOne($order_detail_id)
    {
        $Count                 = App\Modules\Order\Models\OrderImageModel::where('order_detail_id',$order_detail_id)->count();
        return $Count;
    }
}

if (! function_exists('get_OrderImageOne')) {
    /**
     * @param integer
     * @return string
     */
    function get_OrderImageOne($order_detail_id)
    {
        $OrderImage                 = App\Modules\Order\Models\OrderImageModel::where('order_detail_id',$order_detail_id)->get()->first();
        return 'images/item/'.$OrderImage->file;
    }
}

if (! function_exists('get_OrderDetailInfo')) {
    /**
     * @param integer
     * @return string
     */
    function get_OrderDetailInfo($order_detail_id)
    {
        $OrderDetail                 = App\Modules\Order\Models\OrderDetailModel::find($order_detail_id);
        $Info                        = $OrderDetail->treatment->name." ";
        if($OrderDetail->treatment_category_id){
            $Info                   .= $OrderDetail->treatmentcategory->name."- ";
        }
        $Info                       .= $OrderDetail->treatmentpackage->name;
        return $Info;
    }
}

if (! function_exists('get_OrderDetailbyID')) {
    /**
     * @param integer
     * @return string
     */
    function get_OrderDetailbyID($order_id)
    {
        $OrderDetail                 = App\Modules\Order\Models\OrderDetailModel::where('order_id',$order_id)->get();
        return $OrderDetail;
    }
}

if (! function_exists('get_GenerateLinkImage')) {
    /**
     * @param path, image file
     * @return string
     */
    function get_GenerateLinkImage($Path, $File)
    {
        return $Path.$File;
    }
}

if (! function_exists('set_SaldoBranch')) {
    /**
     * @param $BranchID = integer, $Total = float, $Flow = IN/OUT
     * @return boolean
     */
    function set_SaldoBranch($BranchID, $Total, $Flow)
    {
        $Branch                     = App\Modules\Branch\Models\BranchModel::find($BranchID);
        $TotalOld                   = $Branch->saldo_realtime;
        if($Flow == 'IN'){
            $NewTotal               = $TotalOld + $Total;
            $Realtime               = date('Y-m-d H:i:s');
        }else{
            $NewTotal               = $TotalOld - $Total;
            $Realtime               = date('Y-m-d H:i:s');
        }
        $Branch->saldo_realtime     = $NewTotal;
        $Branch->saldo_realtime_date= $Realtime;
        if($Branch->save()){
            return true;
        }else{
            return;
        }
    }
}

if (! function_exists('get_ListStock')) {
    /**
        * @param BranchID
        * @return string
     */
    function get_ListStock($BranchID)
    {
        $Stock                                   = App\Modules\Stock\Models\Stock::where("is_active",1)
                                                                                    ->where("branch_id",$BranchID)
                                                                                    ->where("stock",">",0)
                                                                                    ->get();

        $output = array();
        $output [0] = 'Pilih Nama Barang';
        foreach ($Stock as $item){
                $output[$item->id] = $item->name;
        }
        return $output;

    }
}

if (! function_exists('getStatusArchive')) {
    /**
        * @param BranchID
        * @return string
     */
    function getStatusArchive($StatusID)
    {
        if($StatusID == 0){
            return "DP";
        }else{
            return "Lunas";
        }

    }
}


if (! function_exists('getBranch')) {
    /**
        * @param BranchID,$field
        * @return string
     */
    function getBranch($BranchID,$field)
    {
        $Branch                                     = App\Modules\Branch\Models\BranchModel::find($BranchID);
        return $Branch->$field;

    }
}


if (! function_exists('get_ListMonth')) {
    /**
     * @return string
     */
    function get_ListMonth()
    {

        $output             = array();
        $output [0]         = 'Pilih Bulan';
        $output [1]         = 'Januari';
        $output [2]         = 'Februari';
        $output [3]         = 'Maret';
        $output [4]         = 'April';
        $output [5]         = 'Mei';
        $output [6]         = 'Juni';
        $output [7]         = 'Juli';
        $output [8]         = 'Agustus';
        $output [9]         = 'September';
        $output [10]        = 'Oktober';
        $output [11]        = 'November';
        $output [12]        = 'Desember';
        return $output;

    }
}

if (! function_exists('get_ListTwoYears')) {
    /**
     * @return string
     */
    function get_ListTwoYears()
    {

        $output             = array();
        $yearPrev           = date('Y') - 1;
        $year               = date('Y');
        for($x = $yearPrev; $x<=$year; $x++){
            $output [$x]    = $x;
        }
        return $output;

    }
}



if (! function_exists('ean13_check_digit')) {
    /**
     * @param digit
     * @return string
     */
    function ean13_check_digit($digits)
    {

        //first change digits to a string so that we can access individual numbers
        $digits =(string)$digits;
        // 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
        $even_sum = $digits{1} + $digits{3} + $digits{5} + $digits{7} + $digits{9} + $digits{11};
        // 2. Multiply this result by 3.
        $even_sum_three = $even_sum * 3;
        // 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
        $odd_sum = $digits{0} + $digits{2} + $digits{4} + $digits{6} + $digits{8} + $digits{10};
        // 4. Sum the results of steps 2 and 3.
        $total_sum = $even_sum_three + $odd_sum;
        // 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
        $next_ten = (ceil($total_sum/10))*10;
        $check_digit = $next_ten - $total_sum;
        return $digits . $check_digit;
    }
}


if (! function_exists('get_selectedCount')) {
    /**
     * @return string
     */
    function get_selectedCount()
    {

        $output             = array();
        $output [0]         = "Pilih Jumlah Barcode";
        $output [10]        = "10 Barcode";
        $output [25]        = "25 Barcode";
        $output [50]        = "50 Barcode";
        $output [75]        = "75 Barcode";
        $output [100]       = "100 Barcode";
        $output [150]       = "150 Barcode";
        $output [200]       = "200 Barcode";
        return $output;

    }
}

if (! function_exists('get_jenisTransaksi')) {
    /**
     * @return string
     */
    function get_jenisTransaksi()
    {

        $output             = array();
        $output [""]        = "Pilih Jenis Transaksi";
        $output ["I"]       = "Pendapatan";
        $output ["O"]       = "Pengeluaran";
        return $output;

    }
}


if (! function_exists('get_AccountList')) {
    /**
     * @return string
     */
    function get_AccountList($Flow)
    {
        $Account                                   = App\Modules\Account\Models\Account::where("is_active",'=',1)
            ->where('flow','=',$Flow)
            ->get();

        $output = array();
        foreach ($Account as $item){
            $output[$item->id] = $item->name;
        }
        return $output;
    }
}


if (! function_exists('set_TransactionPoint')) {
    /**
     * @param customer_id, point, flow
     * @return boolean
     */
    function set_TransactionPoint($CustomerID,$Point,$Flow)
    {
        $Pointinfo                              = new App\Modules\Point\Models\Point();

        $Pointinfo->customer_id                 = $CustomerID;
        $Pointinfo->created_by                  = Auth::id();
        $Pointinfo->updated_by                  = Auth::id();
        if($Flow == 'IN'){
            $Pointinfo->debit                   = $Point;
            $Pointinfo->credit                  = 0;
        }else{
            $Pointinfo->debit                   = 0;
            $Pointinfo->credit                  = $Point;
        }
        if($Pointinfo->save()){
            $Customer                           = \App\Modules\Customer\Models\CustomerModel::find($CustomerID);
            if($Flow == 'IN'){
                $Customer->point                = $Customer->point + $Point;
            }else{
                $Customer->point                = $Customer->point - $Point;
            }
            if($Customer->save()){
                return true;
            }
            return false;
        }
        return;
    }
}


if (! function_exists('get_ListSupplier')) {
    /**
     * @return string
     */
    function get_ListSupplier()
    {
        $Supplier                                     = \App\Modules\Supplier\Models\Supplier::where('is_active','=',1)->get();

        $output                                     = array();
        $output [0]                                 = 'Pilih Supplier';
        foreach ($Supplier as $item){
            $output[$item->id]                    = $item->name;
        }
        return $output;

    }
}

?>
