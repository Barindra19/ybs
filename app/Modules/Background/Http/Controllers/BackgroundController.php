<?php

namespace App\Modules\Background\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


use App\Modules\Branch\Models\BranchModel;
use App\Modules\Customer\Models\CustomerModel;
use App\Modules\Stock\Models\Stock as StockModel;
use App\User;

use App\Modules\Role\Models\Role;
use Illuminate\Auth\Events\Registered;
use App\Jobs\SendMail;
use Activity;
use Debugbar;


class BackgroundController extends Controller
{


    public function customer_create_user($BranchID){
        $Branch                             = BranchModel::find($BranchID);
        if($Branch){
            $CustomerList                   = CustomerModel::where('branch_id','=',$BranchID)->where('status','=',1)->get();
            if($CustomerList){
                foreach ($CustomerList as $Customer) {
                    $RefNumber                  = $Customer->ref_number;
                    $Email                      = $Customer->email;
                    $Name                       = $Customer->name;
                    if($Email){
                        if(User::where('email','=',$Email)->count() == 0){
                            ### Save to User, Create Role and Send Mail ###
                            event(new Registered($Users = $this->create($Customer)));
                            $Role                                   = Role::where('name', 'customer')->first();
                            $Users->attachRole($Role->id);          // Customer ID //

                            $EmailParams                            = array(
                                'Subject'                               => "Selamat datang di Your Bag Spa",
                                'Views'                                 => "email.verification_register_customer",
                                'User'                                  => $Users,
                                'To'                                    => $Users->email,
                                'Password'                              => get_default_password()
                            );
                            dispatch(new SendMail($EmailParams));
                            ### Save to User, Create Role and Send Mail ###
                        }
                    }
                }

            }
        }else{
            $Details = array("BranchID" => $BranchID);
            Activity::log([
                    'contentId'   => $BranchID,
                    'contentType' => 'background',
                    'action'      => 'customer_create_user',
                    'description' => "Data Branch Tidak ditemukan",
                    'details'     => json_encode($Details),
                    'updated'     => 0,
                ]);

        }
    }

    protected function create($Customer){
        return User::create([
            'name'                  => $Customer->name,
            'email'                 => $Customer->email,
            'password'              => bcrypt(get_default_password()),
            'is_active'             => 1,
            'is_lock'               => 0,
            'email_token'           => base64_encode($Customer->email),
            'branch_id'             => $Customer->branch_id
        ]);
    }

    public function changed_stock_code(){
        $Stock                  = StockModel::all();
        foreach ($Stock as $item){
            $id                     = $item->id;
            $Code                   = "ITM8".date("ymd").sprintf("%04s",$id);
            $StockUpdate            = StockModel::find($id);
            $StockUpdate->code      = $Code;
            try{
                $StockUpdate->save();
            }catch (\Exception $e) {
                $Details = array(
                    "id"            => $id,
                    "code"          => $Code
                );
                Activity::log([
                    'contentId'   => $id,
                    'contentType' => 'background',
                    'action'      => 'changed_stock_code',
                    'description' => "Data Stock Item Tidak ditemukan",
                    'details'     => json_encode($Details),
                    'updated'     => 0,
                ]);
            }
        }
    }


}
