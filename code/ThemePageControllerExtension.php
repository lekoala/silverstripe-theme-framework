<?php

/**
 * ThemePageControllerExtension
 *
 * @author lekoala
 */
class ThemePageControllerExtension extends Extension
{

    public function onBeforeInit()
    {
        // Theme is not yet defined properly at this time
    }

    public function onAfterInit()
    {
        if (Director::isDev()) {
            Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
            Requirements::block(THIRDPARTY_DIR.'/jquery/jquery.min.js');
        } else {
            Requirements::block(THIRDPARTY_DIR.'/jquery/jquery.js');
            Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.min.js');
        }

        $themeDir = SSViewer::get_theme_folder();
        Requirements::javascript($themeDir.'/javascript/init.js');
    }
}