<?php

namespace App\Modules\User\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


use File;
use App\Modules\Customer\Models\CustomerModel;
use App\User;
use Theme;
use Auth;
use Activity;


class ProfileController extends Controller
{
    protected $_data                                = array();

    public function __construct()
    {
        $this->_data['string_menuname']             = 'Profile';

    }

    public function showInfoForm(){
        $this->_data['form']                        = 'info';
        $this->_data['form_name']                   = 'Member Info';

        $this->_data['Customer']                    = CustomerModel::where('user_id','=',Auth::id())->first();
        return Theme::view('modules.user.profile.info',$this->_data);
    }

    public function actionInfoForm(Request $request){
        $validator = Validator::make($request->all(), [
            'name'                  => 'required',
            'address'               => 'required',
            'phone'                 => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $Customer                                   = CustomerModel::find($request->id);
        $Customer->name                             = $request->name;
        $Customer->address                          = $request->address;
        $Customer->phone                            = $request->phone;

        try {
            $Customer->save();
            return redirect()
                ->route('profile_info')
                ->with('ScsMsg',"Your Profile Info has been updated.");
        }
        catch (\Exception $e) {
            $Detail                                 = array(
                'name'                      => $request->name,
                'address'                   => $request->address,
                'phone'                     => $request->phone
            );
            Activity::log([
            		'contentId'   => $request->id,
            		'contentType' => 'Member Info',
            		'action'      => 'save',
            		'description' => $e->getMessage(),
            		'details'     => "ada kesalahan pada saat input data".json_encode($Detail),
            		'updated'     => Auth::id(),
            	]);
            return redirect()
                ->route('profile_info')
                ->with('ErrMsg',"Ada Kesalahan teknis. Mohon hubungi cs@yourbagspa.com");
        }
    }

    public function showChangedInForm(){
        $this->_data['form']                        = 'changed_password';
        $this->_data['form_name']                   = 'Changed Password';

        return Theme::view('modules.user.profile.changed_password',$this->_data);
    }

    public function actionChangedInForm(Request $request){
        $validator = Validator::make($request->all(), [
            'password'              => 'required|confirmed'
        ]);

        if ($validator->fails()) {
             return redirect()
                        ->route('profile_changed_password')
                        ->withErrors($validator)
                        ->withInput();
        }

        $new_password                               = $request->password;

        $obj_user                                   = User::find(Auth::id());
        $obj_user->password                         = Hash::make($new_password);
        if($obj_user->save()){
            return redirect()
                ->route('profile_info')
                ->with('ScsMsg',"Password berhasil diubah.");
        }

    }

}
