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
        if (Director::isDev()) {
            Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
            Requirements::block(THIRDPARTY_DIR.'/jquery/jquery.min.js');
        } else {
            Requirements::block(THIRDPARTY_DIR.'/jquery/jquery.js');
            Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.min.js');
        }
    }

    public function onAfterInit()
    {
        $themeDir = SSViewer::get_theme_folder();
        $config   = SiteConfig::current_site_config();
        if ($config->Theme) {
            $themeDir = THEMES_DIR.'/'.$config->Theme;

            // Properly update theme if set in config to make themedCSS work properly
            Config::inst()->update('SSViewer', 'theme', $config->Theme);
        }
        $stylesPath = $config->StylesPath();
        $stylesFile = Director::baseFolder().$stylesPath;

        if (is_file($stylesFile)) {
            // We use compiled file
            Requirements::css(trim($stylesPath, '/'));
        } else {
            // We use theme file
            Requirements::themedCSS('all');
        }
        Requirements::javascript($themeDir.'/javascript/init.js');
    }
}