<?php

namespace App\Extensions\Twig;
//use Stolz\Assets\Laravel\Facade as Assets;
use Stolz\Assets\Manager as Assets;
use \Debugbar;

use Illuminate\Foundation\Application;
use Illuminate\Support\Str as IlluminateStr;
use Twig_Environment;
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_SimpleFilter;

use Caffeinated\Themes\Themes as Theme;

class TwigAssets extends Twig_Extension
{

    protected $asset;
    protected $theme;

    public function __construct(Assets $asset, Theme $theme)
    {
        $this->asset = $asset;
        $this->theme = $theme;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return array(
            new Twig_SimpleFunction(
                'assets_*',
                function ($name) {
                    $arguments = array_slice(func_get_args(), 1);
                    $name      = IlluminateStr::camel($name);

                    return call_user_func_array([$this->asset, $name], $arguments);
                }
            ),
            new Twig_SimpleFunction(
                'theme_*',
                function ($name) {
                    $arguments = array_slice(func_get_args(), 1);
                    $name      = IlluminateStr::camel($name);
                    return call_user_func_array([$this->theme, $name], $arguments);
                }
            )
        );
    }

    /**
     * getFilters
     * @return array
     */
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter('css_tag', [$this, 'css_tag'],['is_safe' => ['html']]),
            new Twig_SimpleFilter('script_tag', [$this, 'script_tag'], ['is_safe' => ['html']]),
            new Twig_SimpleFilter('menus', [$this, 'menus'], ['is_safe' => ['html']])

        );
    }

    /**
     * Css Tag Filters
     * @author Yusuf
     * @param $link
     * @param array $attr
     * @return string
     */
    public function css_tag($link, $attr = array()) {
        if(!empty($attr)) {

        }

        return '<link href="' . $link . '"  type="text/css" rel="stylesheet" />';
    }

    /**
     * Script tag filters
     * @author Yusuf
     * @param $link
     * @param array $attr
     * @return string
     */
    public function script_tag($link, $attr = array()) {
        if(!empty($attr)) {

        }

        return '<script src="' . $link . '"></script>';
    }

    public function menus() {

        return 'tesss';
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Stolz/Assets Integration';
    }
}
