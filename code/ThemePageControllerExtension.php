<?php

/**
 * ThemePageControllerExtension
 *
 * @author lekoala
 */
class ThemePageControllerExtension extends Extension
{

    /**
     * Helper to detect if we are in admin or development admin
     * 
     * @return boolean
     */
    public function isAdminBackend()
    {
        if (
            $this->owner instanceof LeftAndMain ||
            $this->owner instanceof DevelopmentAdmin ||
            $this->owner instanceof DatabaseAdmin ||
            (class_exists('DevBuildController') && $this->owner instanceof DevBuildController)
        ) {
            return true;
        }

        return false;
    }

    public static function config()
    {
        return Config::inst()->forClass(__CLASS__);
    }

    public function onBeforeInit()
    {
        // Theme is not yet defined properly at this time

        if ($this->isAdminBackend()) {
            return;
        }

        $conf = self::config();
        if ($conf->include_jquery) {
            if (Director::isDev()) {
                Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.js');
                Requirements::block(THIRDPARTY_DIR.'/jquery/jquery.min.js');
            } else {
                Requirements::block(THIRDPARTY_DIR.'/jquery/jquery.js');
                Requirements::javascript(THIRDPARTY_DIR.'/jquery/jquery.min.js');
            }
        }
        if ($conf->include_jquery_ui) {
            if (Director::isDev()) {
                Requirements::javascript(THIRDPARTY_DIR.'/jquery-ui/jquery-ui.js');
                Requirements::block(THIRDPARTY_DIR.'/jquery-ui/jquery-ui.min.js');
            } else {
                Requirements::block(THIRDPARTY_DIR.'/jquery-ui/jquery-ui.js');
                Requirements::javascript(THIRDPARTY_DIR.'/jquery-ui/jquery-ui.min.js');
            }
            if ($conf->jquery_ui_theme) {
                Requirements::block(THIRDPARTY_DIR.'/jquery-ui-themes/smoothness/jquery-ui.css');
                Requirements::css($conf->jquery_ui_theme);
            } else {
                Requirements::css(THIRDPARTY_DIR.'/jquery-ui-themes/smoothness/jquery-ui.css');
            }
        }
    }

    public function onAfterInit()
    {
        if ($this->isAdminBackend()) {
            return;
        }

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