<?php
/**
 * Created by PhpStorm.
 * User: yusufmulhajat
 * Date: 5/31/17
 * Time: 10:35 PM
 */

namespace App\Extensions\Twig;

use Illuminate\Support\Str as IlluminateStr;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;

use Illuminate\Http\Request;

use \Debugbar;

class TwigDefaultHelper extends Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('active_class_path',[$this, 'active_class_path'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('call_helpers',[$this, 'call_helpers'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('call_helpers_two',[$this, 'call_helpers_two'], ['is_safe' => ['html']]),
            new Twig_SimpleFunction('call_helpers_three',[$this, 'call_helpers_three'], ['is_safe' => ['html']]),
        );
    }

    public function active_class_path($paths, $classes = null)
    {
//        Debugbar::info(call_user_func_array('Request::is', (array)$paths));
        return call_user_func_array('Request::is', (array)$paths) ? $classes : '';
    }

    public function call_helpers($function, $Params = ""){
        return $function($Params);
    }
    public function call_helpers_two($function, $Param1 = "", $Param2 = ""){
        return $function($Param1,$Param2);
    }

    public function call_helpers_three($function, $Param1 = "", $Param2 = "", $Param3){
        return $function($Param1,$Param2,$Param3);
    }

}