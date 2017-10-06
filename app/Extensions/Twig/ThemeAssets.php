<?php

namespace App\Extensions\Twig;
use Caffeinated\Themes\Facades\Theme as Theme;

class ThemeAssets extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
          new \Twig_SimpleFunction('theme_asset', [$this, 'theme_asset'],['is_safe' => ['html']]),
        );
    }

    public function theme_asset()
    {
        return Theme::getActive();
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Caffeinated/Theme Integration';
    }
}
