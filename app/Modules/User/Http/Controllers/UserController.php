<?php

namespace App\Modules\User\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\View\View;
use App\Modules\User\Models\UserModel;
use App\Modules\Role\Models\Role;
use Illuminate\Support\Facades\DB;
use App\Modules\Emailtemplate\Models\EmailTemplate as EmailTemplateModel;

use Auth;
use Theme;
use App\User;

use App\Jobs\SendMail;



class UserController extends Controller
{
    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:user-management-view']);
        $this->middleware('permission:user-management-add')->only('add');
        $this->middleware('permission:user-management-edit')->only('edit');
        $this->middleware('permission:user-management-inactive')->only('inactive');

        $this->_data['string_menuname']             = 'User Management';
        $this->_data['IDMENU']                      = 'User';


    }


    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'branch';
        $this->_data['IDSUBMENU']                   = 'ListUser';

        return Theme::view('modules.user.show',$this->_data);
    }

    public function show(){

        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'user';
        $this->_data['IDSUBMENU']                   = 'ListUser';

        return Theme::view('modules.user.show',$this->_data);
    }

    public function datatables(){
        $User = UserModel::join('role_user',"users.id","=","role_user.user_id")
                        ->join("roles",'role_user.role_id',"=","roles.id")
                        ->join("branchs",'branchs.id',"=","users.branch_id")
                        ->select(['users.id', 'users.name as name','users.email as email','roles.name as roles','users.is_active','branchs.name as branch']);

        return Datatables::of($User)
            ->addColumn('href', function ($User) {
                return '<a href="'.route('user_edit',$User->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>
                        <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$User->id.')"><i class="fa fa-ban"></i></a>
                        <a href="'.route('user_changed_password',$User->id).'" class="btn btn-warning"><i class="fa fa-unlock-alt"></i></a>
                        ';
            })
            ->editColumn('is_active', function ($User) {
                if($User->is_active == 1){
                    return '<span class="label label-sm label-success">'.get_active($User->is_active).'</span>';
                }else{
                    return '<span class="label label-sm label-danger">'.get_active($User->is_active).'</span>';
                }
            })

            ->rawColumns(['href','is_active'])
            ->make(true);
    }


    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddUser';

        $Users                                      = Auth::user();
        $this->_data['BranchID']                    = $Users->branch_id;


        return Theme::view('modules.user.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';
        $this->_data['IDSUBMENU']                   = 'AddUser';

        $this->_data['id']                          = $request->id;
        $User                                       = UserModel::find($request->id);

        $this->_data['User']                        = $User;

        $obj_Roles                                      = DB::table('role_user')
                                                            ->join('roles', 'roles.id', '=', 'role_user.role_id')
                                                            ->where('role_user.user_id', $request->id)
                                                            ->select('roles.id', 'roles.name')
                                                            ->get()->first();


        $this->_data['RoleID']                      = $obj_Roles->id;
        $this->_data['RoleName']                    = $obj_Roles->name;


        return Theme::view('modules.user.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'branch'    => 'required',
            'role'      => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $User                                       = new UserModel();
        $User->name                                 = $request->name;
        $User->email                                = $request->email;
        $User->password                             = bcrypt(get_default_password());
        $User->is_active                            = 1;
        $User->is_lock                              = 0;
        $User->email_token                          = base64_encode($request->email);
        $User->branch_id                            = $request->branch;

        if($User->save()){

            $Users                                  = UserModel::find($User->id);

            ### SET ROLES ###
            DB::table('role_user')->insert([
                        'user_id' => $User->id,
                        'role_id' => $request->role,
                    ]);
            ### SET ROLES ###
            $EmailFormat                            = EmailTemplateModel::where('name','=','VERIFICATION_REGISTER')->first();
            $LinkVerificationRegister               = '<a href="'.route("users_request_reset_password",$Users->email_token).'" > '.route("users_request_reset_password",$Users->email_token).'</a>';
            $Body                                   = $EmailFormat->template;
            $Body                                   = str_replace("@FULLNAME",$Users->name,$Body);
            $Body                                   = str_replace("@EMAIL",$Users->email,$Body);
            $Body                                   = str_replace("@LINKVERIFICATIONREGISTER",$LinkVerificationRegister,$Body);

            $EmailParams                            = array(
                'Subject'                               => "Selamat datang di Your Bag Spa",
                'Views'                                 => "email.verification_register",
                'User'                                  => $Users,
                'To'                                    => $Users->email,
                'Body'                                  => $Body,
                'Password'                              => get_default_password(),
                'attachment'                            => '' // required
            );

            dispatch(new SendMail($EmailParams));


            return redirect()
                ->route('user_show')
                ->with('scsMsg',"Data successfuly saving");
        }
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name'      => 'required',
            'role'      => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $User                                       = UserModel::find($request->id);
        $User->name                                 = $request->name;
        $User->branch_id                            = $request->branch;
        if($User->save()){
            DB::table('role_user')->where('user_id',$request->id)->delete();
            $Users                                  = User::find($request->id);
            $Users->attachRole($request->role);
            return redirect()
                ->route('user_show')
                ->with('scsMsg',"Data succesfuly update");
        }
    }

    public function inactive(Request $request){
        $User                                       = UserModel::find($request->id);
        $User->is_active                            = 0;
        $User->updated_by                           = Auth::id();
        if($User->save()){
            return redirect()
                ->route('user_show')
                ->with('scsMsg',"Branch succesful inactive");

        }else{
            dd("Error data inactive");
        }
    }

    public function changed_password($id){
        $this->_data['id']                          = $id;
        $this->_data['Users']                       = User::find($id);

        return Theme::view('modules.user.changed_password.form',$this->_data);
    }

    public function changed_password_act(Request $request){
        $id                                         = $request->id;
        $name                                       = $request->name;
        $validator = Validator::make($request->all(), [
            'email'                 => 'email|max:100',
            'password'              => 'required|confirmed'
        ]);

        if ($validator->fails()) {
             return redirect()
                        ->route('user_changed_password',$id)
                        ->withErrors($validator)
                        ->withInput();
        }
        $new_password                               = $request->password;

        $obj_user                                   = User::find($id);
        $obj_user->password                         = Hash::make($new_password);
        if($obj_user->save()){
            return redirect()
                ->route('user_show')
                ->with('scsMsg',"Password ".$name." berhasil diubah.");
        }



    }

}
