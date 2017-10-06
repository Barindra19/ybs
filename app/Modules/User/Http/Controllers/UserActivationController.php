<?php

namespace App\Modules\User\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;


class UserActivationController extends Controller
{
    public function activateUser($token){
        $user               = User::where('email_token',$token)->first();

        $user->is_active    = 1;
        if($user->save()) {
            return redirect()
                ->route('login')
                ->with('scsMsg',"Activation account successfully!");
        }
    }
}
