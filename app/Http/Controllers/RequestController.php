<?php

namespace App\Http\Controllers;

use App\Modules\Emailtemplate\Models\EmailTemplate;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\User;
use Theme;

use App\Jobs\SendMail;


class RequestController extends Controller
{
    public function password_request(){
        return Theme::view('password_request');
    }

    public function form_password_request($token){
        $email                          =  base64_decode($token);
        $Users                          = User::where('email','=',$email)->get()->first();
        $this->_data['id']              = $Users->id;
        $this->_data['Users']           = $Users;

        return Theme::view('form_changed_request', $this->_data);
    }

    public function password_requestact(Request $request){
        $validator = Validator::make($request->all(), [
            'email'                 => 'email|max:100',
            'password'              => 'required|confirmed'
        ]);
        $email                      = base64_encode($request->email);
        $user_id                    = $request->user_id;
        $new_password               = $request->password;

        if ($validator->fails()) {
             return redirect()
                        ->route('users_request_reset_password',$email)
                        ->withErrors($validator)
                        ->withInput();
        }

        $obj_user = User::find($user_id);
        $obj_user->password = Hash::make($new_password);
        if($obj_user->save()){
            return redirect()
                ->route('login')
                ->with('scsMsg',"Password anda berhasil diubah.");
        }
    }

    public function password_requestsend(Request $request){
        $email          = $request->username;

        $validator = Validator::make($request->all(), [
            'username'                 => 'email|max:100|required',
        ]);

        if ($validator->fails()) {
             return redirect()
                        ->route('password_request')
                        ->withErrors($validator)
                        ->withInput();
        }
        if(User::where('email','=',$email)->count() > 0){
            $Users                                      = User::where('email','=',$email)->get()->first();

            $EmailFormat                            = EmailTemplate::where('name','=','RESET_PASSWORD')->first();
            $LinkResetPassword                      = '<a href="'.route("users_request_reset_password", $Users->email_token).'" > '.route("users_request_reset_password", $Users->email_token).'</a>';
            $Body                                   = $EmailFormat->template;
            $Body                                   = str_replace("@FULLNAME",$Users->name,$Body);
            $Body                                   = str_replace("@EMAIL",$Users->email,$Body);
            $Body                                   = str_replace("@LINKRESETPASSWORD",$LinkResetPassword,$Body);

            $EmailParams                                = array(
                'Subject'                               => "Reset Password Customer Portal",
                'Views'                                 => "email.request_password",
                'Users'                                 => $Users,
                'To'                                    => $email,
                'Body'                                  => $Body,
                'attachment'                            => ''
            );
            dispatch(new SendMail($EmailParams));
            return redirect()
                ->route('login')
                ->with('scsMsg','Please check your email.');
        }else{
            return redirect()
                ->back()
                ->with('errMsg',"Email tidak terdaftar");
        }

    }

}
