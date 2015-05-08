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
    // Uikit notify types
    const NOTIFY_SUCCESS  = 'success';
    const NOTIFY_INFO     = 'info';
    const NOTIFY_DANGER   = 'danger';
    const NOTIFY_WARNING  = 'warning';

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
            $member = Member::currentUser();

            // Silverstripe does not redirect if invalid login to the /admin section so layout will be broken
            if ($member && $member->ID) {
                $access = Permission::checkMember($member, 'CMS_ACCESS_CMSMain');
                if (!$access) {
                    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : Director::baseURL();
                    Session::set("Security.Message.message", _t('Security.ALREADYLOGGEDIN'));
                    Session::set("Security.Message.type", 'warning');
                    Session::set("BackURL", $uri);
                    Session::save();
                    header('Location:' . Director::absoluteBaseURL() . '/Security/login' . "?BackURL=" . urlencode($uri));
                    exit();
                }
            }
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
            FormExtraJquery::include_jquery();
        }
        if ($conf->include_jquery_ui) {
            FormExtraJquery::include_jquery_ui();
        }

        $uikit = $conf->uikit;
        if ($uikit && $uikit['enabled']) {
            $uikitTheme = 'uikit';
            if ($uikit['theme']) {
                $uikitTheme .= '.'.$uikit['theme'];
            }
            $uikitComponents = $uikit['components'];
            if (Director::isDev()) {
                Requirements::javascript(THEME_FRAMEWORK_PATH.'/uikit/js/uikit.js');
                if ($uikit['theme_enabled']) {
                    Requirements::css(THEME_FRAMEWORK_PATH.'/uikit/css/'.$uikitTheme.'.css');
                }
                foreach ($uikitComponents as $component) {
                    Requirements::javascript(THEME_FRAMEWORK_PATH.'/uikit/js/components/'.$component.'.js');
                    if ($uikit['theme_enabled']) {
                        $componentTheme = '';
                        if ($uikit['theme']) {
                            $componentTheme = '.'.$uikit['theme'];
                        }
                        Requirements::css(THEME_FRAMEWORK_PATH.'/uikit/css/components/'.$component.$componentTheme.'.css');
                    }
                }
            } else {
                Requirements::javascript(THEME_FRAMEWORK_PATH.'/uikit/js/uikit.min.js');
                if ($uikit['theme_enabled']) {
                    Requirements::css(THEME_FRAMEWORK_PATH.'/uikit/css/'.$uikitTheme.'.min.css');
                }
                foreach ($uikitComponents as $component) {
                    Requirements::javascript(THEME_FRAMEWORK_PATH.'/uikit/js/components/'.$component.'.min.js');
                    if ($uikit['theme_enabled']) {
                        $componentTheme = '';
                        if ($uikit['theme']) {
                            $componentTheme = '.'.$uikit['theme'];
                        }
                        Requirements::css(THEME_FRAMEWORK_PATH.'/uikit/css/components/'.$component.$componentTheme.'.min.css');
                    }
                }
            }

            // If we loaded notify
            if (in_array('notify', $uikitComponents)) {
                if ($this->owner->hasMethod('SessionMessage') && $this->owner->SessionMessage(false)) {
                    $message = $this->owner->SessionMessage();

                    $content = Convert::raw2js($message->Content);
                    $type    = Convert::raw2js($message->Type);

                    // Convert default Silverstripe types
                    switch ($type) {
                        case self::MESSAGE_BAD:
                            $type = self::NOTIFY_DANGER;
                            break;
                        case self::MESSAGE_GOOD:
                            $type = self::NOTIFY_SUCCESS;
                            break;
                        case self::MESSAGE_WARNING:
                            $type = self::NOTIFY_WARNING;
                            break;
                        case self::MESSAGE_INFO:
                            $type = self::NOTIFY_INFO;
                            break;
                    }

                    Requirements::customScript(<<<JS
UIkit.notify('$content',{
  status: '$type',
  timeout: 0
});
JS
                    );
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

        // Refresh theme also on flush
        $dev = Director::isDev();
        if (filter_input(INPUT_GET, 'flush') && Permission::check('ADMIN')) {
            $dev = true;
        }

        // Refresh theme files if updated in dev
        if ($dev && !$disabled) {
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
}