<?php

namespace App\Modules\Emailtemplate\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Yajra\Datatables\Facades\Datatables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\View\View;

use App\Modules\Emailtemplate\Models\EmailTemplate as EmailTemplateModel;
use Auth;
use Theme;
use Activity;


class EmailTemplateController extends Controller
{

    protected $_data = array();
    public function __construct()
    {
        $this->middleware(['permission:emailtemplate-view']);
        $this->middleware('permission:emailtemplate-add')->only('add');
        $this->middleware('permission:emailtemplate-edit')->only('edit');
        $this->middleware('permission:emailtemplate-delete')->only('delete');

        $this->_data['string_menuname']             = 'Email Template';
    }

    public function index(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'emailtemplate';

        return Theme::view('modules.emailtemplate.show',$this->_data);
    }

    public function show(){
        $this->_data['string_active_menu']          = 'List';
        $this->_data['datatables']                  = 'emailtemplate';
        $this->_data['DateReset']                   = date('d-m-Y');

        return Theme::view('modules.emailtemplate.show',$this->_data);
    }

    public function datatables(){
        $EmailTemplate = EmailTemplateModel::select(['id', 'name']);

        return Datatables::of($EmailTemplate)
            ->addColumn('href', function ($EmailTemplate) {
                    $Edit       = '';
                    $Delete     = '';
                if(bool_CheckAccessUser('emailtemplate-edit')){
                    $Edit       .= '<a href="'.route('emailtemplate_edit',$EmailTemplate->id).'" class="btn btn-info"><i class="glyphicon glyphicon-pencil"></i></a>&nbsp;';
                }
                if(bool_CheckAccessUser('emailtemplate-delete')){
                    $Delete     .= '<a href="javascript:void(0);" class="btn btn-danger" onclick="deleteList('.$EmailTemplate->id.')"><i class="fa fa-ban"></i></a>&nbsp;';
                }
                return $Edit.$Delete;
            })

            ->rawColumns(['href'])
            ->make(true);
    }

    public function add(){
        $this->_data['state']                       = 'add';
        $this->_data['string_active_menu']          = 'Add/Edit';

        return Theme::view('modules.emailtemplate.form',$this->_data);
    }

    public function edit(Request $request){
        $this->_data['state']                       = 'edit';
        $this->_data['string_active_menu']          = 'Add/Edit';

        $this->_data['id']                          = $request->id;
        $EmailTemplate                              = EmailTemplateModel::find($request->id);

        $this->_data['EmailTemplate']               = $EmailTemplate;

        return Theme::view('modules.emailtemplate.form',$this->_data);
    }

    public function post(Request $request){
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'template'      => 'required',
            'notes'         => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }

        $EmailTemplate                              = new EmailTemplateModel();
        $EmailTemplate->name                        = $request->name;
        $EmailTemplate->template                    = $request->template;
        $EmailTemplate->notes                       = $request->notes;
        $EmailTemplate->created_by                  = Auth::id();
        $EmailTemplate->updated_by                  = Auth::id();

        try{
            $EmailTemplate->save();
            return redirect()
                ->route('emailtemplate_show')
                ->with('ScsMsg',"Data successfuly saving");
        }
        catch (\Exception $e) {
            $Post                                   = array(
                "name"                  => $request->name,
                "template"              => $request->template,
                "notes"                 => $request->notes,
                "created_by"            => Auth::id()
            );
            Activity::log([
                'contentId'   => 0,
                'contentType' => 'Email Template',
                'action'      => 'post',
                'description' => $e->getMessage(),
                'details'     => json_encode($Post),
                'updated'     => Auth::id(),
            ]);
            return redirect()
                ->route('emailtemplate_show')
                ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer");
        }
    }


    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'template'      => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->input());
        }
        $id                                         = $request->id;
        $EmailTemplate                              = EmailTemplateModel::find($request->id);
        $EmailTemplate->template                    = $request->template;
        $EmailTemplate->created_by                  = Auth::id();
        $EmailTemplate->updated_by                  = Auth::id();

        try{
            $EmailTemplate->save();
            return redirect()
                ->route('emailtemplate_show')
                ->with('ScsMsg',"Data successfuly updated");
        }
        catch (\Exception $e) {
            $Post                                   = array(
                "id"                    => $id,
                "name"                  => $request->name,
                "template"              => $request->template,
                "notes"                 => $request->notes,
                "updated_by"            => Auth::id()
            );
            Activity::log([
                'contentId'   => $id,
                'contentType' => 'Email Template',
                'action'      => 'update',
                'description' => $e->getMessage(),
                'details'     => json_encode($Post),
                'updated'     => Auth::id(),
            ]);
            return redirect()
                ->route('emailtemplate_show')
                ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer");
        }
    }

    public function delete(Request $request){
        $EmailTemplate                              = EmailTemplateModel::find($request->id);
        try{
            $EmailTemplate->delete();
            return redirect()
                ->route('emailtemplate_show')
                ->with('ScsMsg',"Data successfuly deleted");
        }
        catch (\Exception $e) {
            $Post                                   = array(
                "id"                    => $id
            );
            Activity::log([
                'contentId'   => $id,
                'contentType' => 'Email Template',
                'action'      => 'update',
                'description' => $e->getMessage(),
                'details'     => json_encode($Post),
                'updated'     => Auth::id(),
            ]);
            return redirect()
                ->route('emailtemplate_show')
                ->with('ErrMsg',"Maaf, ada kesalahan teknis. Mohon hubungi web developer");

        }
    }
}
