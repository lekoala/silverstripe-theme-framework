<?php

/**
 * ThemePageControllerExtension
 *
 * @author lekoala
 */
class ThemePageControllerExtension extends Extension
{
    // Base silverstripe message types
    const MESSAGE_GOOD    = 'good';
    const MESSAGE_BAD     = 'bad';
    const MESSAGE_WARNING = 'warning';
    const MESSAGE_INFO    = 'info';
    // Base Noty types
    const NOTY_SUCCESS    = 'success';
    const NOTY_ERROR      = 'error';
    const NOTY_ALERT      = 'alert';
    const NOTY_INFO       = 'information';
    const NOTY_CONFIRM    = 'confirm';

    /**
     * @config
     * @var boolean
     */
    private static $include_jquery;

    /**
     * @config
     * @var boolean
     */
    private static $include_jquery_ui;

    /**
     * @config
     * @var string
     */
    private static $jquery_ui_theme;

    /**
     * @config
     * @var array
     */
    private static $uikit;

    /**
     * @config
     * @var array
     */
    private static $noty;

    /**
     * @config
     * @var array
     */
    private static $outdated_browser;

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

        $conf = $this->config();

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

        $uikit = $conf->uikit;
        if ($uikit && $uikit['enabled']) {
            $uikitTheme = 'uikit';
            if ($uikit['theme']) {
                $uikitTheme .= '.'.$uikit['theme'];
            }
            if (Director::isDev()) {
                Requirements::javascript(THEME_FRAMEWORK_PATH.'/uikit/js/uikit.js');
                if ($uikit['theme_enabled']) {
                    Requirements::css(THEME_FRAMEWORK_PATH.'/uikit/css/'.$uikitTheme.'.css');
                }
            } else {
                Requirements::javascript(THEME_FRAMEWORK_PATH.'/uikit/js/uikit.min.js');
                if ($uikit['theme_enabled']) {
                    Requirements::css(THEME_FRAMEWORK_PATH.'/uikit/css/'.$uikitTheme.'.min.css');
                }
            }
        }

        $noty = $conf->noty;
        if ($noty && $noty['enabled']) {
            if (Director::isDev()) {
                Requirements::javascript(THEME_FRAMEWORK_PATH.'/javascript/noty/packaged/jquery.noty.packaged.js');
            } else {
                Requirements::javascript(THEME_FRAMEWORK_PATH.'/javascript/noty/packaged/jquery.noty.packaged.min.js');
            }

            $theme  = $noty['theme'];
            $layout = $noty['layout'];
            Requirements::css(THEME_FRAMEWORK_PATH.'/javascript/noty/themes/'.$theme.'.css');

            Requirements::customScript(<<<JS
jQuery.extend(jQuery.noty.defaults,{
  theme: '$theme',
  layout: '$layout',
  closeWith: ['click','button']
});
JS
            );
            // Flash messages
            if ($this->owner->hasMethod('SessionMessage') && $this->owner->SessionMessage(false)) {
                $message = $this->owner->SessionMessage();

                $content = Convert::raw2js($message->Content);
                $type    = Convert::raw2js($message->Type);

                // Convert default Silverstripe types
                switch ($type) {
                    case self::MESSAGE_BAD:
                        $type = self::NOTY_ERROR;
                        break;
                    case self::MESSAGE_GOOD:
                        $type = self::NOTY_SUCCESS;
                        break;
                    case self::MESSAGE_WARNING:
                        $type = self::NOTY_ALERT;
                        break;
                    case self::MESSAGE_INFO:
                        $type = self::NOTY_INFO;
                        break;
                }

                Requirements::customScript(<<<JS
noty({
  text: '$content',
  type: '$type',
  timeout: false
});
JS
                );
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

        $disabled = false;
        if (defined('THEME_FRAMEWORK_DISABLE_COMPILE')) {
            $disabled = true;
        }

        // Refresh theme files if updated in dev
        if (Director::isDev() && !$disabled) {
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

        if (!$disabled && is_file($stylesFile)) {
            // We use compiled file
            Requirements::css(trim($stylesPath, '/'));
        } else {
            // We use theme file
            Requirements::themedCSS('all');
        }
        Requirements::javascript($themeDir.'/javascript/init.js');
    }

    /**
     * Set a session message that will be displayed by messenger on the next load
     * (useful after a redirect)
     *
     * @param string $message
     * @param string $type
     */
    public static function SetSessionMessage($message, $type = 'good')
    {
        Session::set('SessionMessage',
            array(
            'Type' => $type,
            'Content' => $message
        ));
    }

    /**
     * Get and clear session message
     * @param bool $clear
     * @return \ArrayData|boolean
     */
    public static function SessionMessage($clear = true)
    {
        $msg = Session::get('SessionMessage');
        if (!$msg) {
            return false;
        }
        if ($clear) {
            Session::clear('SessionMessage');
        }
        return new ArrayData($msg);
    }

    /**
     * Return "link", "current" or section depending on if this page is the current page, or not on the current page but
     * in the current section.
     *
     * @return string
     */
    public function UKLinkingMode()
    {
        if ($this->isCurrent()) {
            return 'uk-active';
        } elseif ($this->isSection()) {
            return 'uk-parent';
        } else {
            return 'uk-link';
        }
    }
}