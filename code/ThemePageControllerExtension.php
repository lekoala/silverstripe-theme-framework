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
        /* @var $owner Controller */
        $owner = $this->owner;
        if (strpos($owner->getRequest()->getURL(), 'admin/') === 0) {
            return true;
        }
        // Because keep-alive pings done through ajax could trigger requirements loading
        if (strpos($owner->getRequest()->getURL(), 'Security/ping') === 0) {
            return true;
        }
        if (
            $owner instanceof LeftAndMain ||
            $owner instanceof DevelopmentAdmin ||
            $owner instanceof DatabaseAdmin ||
            (class_exists('DevBuildController') && $owner instanceof DevBuildController)
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

        $outdated = $conf->outdated_browser;
        if ($outdated && $outdated['enabled']) {
            if (Director::isDev()) {
                ThemeHeadRequirements::javascript(THEME_FRAMEWORK_PATH.'/javascript/outdatedbrowser/outdatedbrowser.js');
                Requirements::css(THEME_FRAMEWORK_PATH.'/javascript/outdatedbrowser/outdatedbrowser.css');
            } else {
                ThemeHeadRequirements::javascript(THEME_FRAMEWORK_PATH.'/javascript/outdatedbrowser/outdatedbrowser.min.js');
                Requirements::css(THEME_FRAMEWORK_PATH.'/javascript/outdatedbrowser/outdatedbrowser.min.css');
            }
            ThemeHeadRequirements::javascriptTemplate(THEME_FRAMEWORK_PATH.'/javascript/outdated.js',
                array(
                'BgColor' => $outdated['bg_color'],
                'Color' => $outdated['color'],
                'LowerThan' => $outdated['lower_than'],
//                'LowerThan' => 'IE12',
                'Lang' => i18n::get_lang_from_locale(i18n::get_locale())
            ));
        }

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

        // Forcing js to bottom allow to put some scripts tags in the head if we want to
        Requirements::set_force_js_to_bottom(true);
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

        // Refresh theme files if updated in dev
        if (Director::isDev()) {
            if (is_file($stylesFile)) {
                $timeCompiled = filemtime($stylesFile);
            } else {
                $timeCompiled = 0;
            }
            $baseCss = Director::baseFolder().'/'.$themeDir.'/css/all.css';
            if (!is_file($baseCss)) {
                return;
            }
            $timeOriginal = filemtime($baseCss);

            // We need to recompile the styles
            if ($timeOriginal > $timeCompiled) {
                $config->compileStyles();
            }
        }

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