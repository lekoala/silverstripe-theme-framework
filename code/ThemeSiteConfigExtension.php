<?php

/**
 * ThemeSiteConfigExtension
 *
 * @author lekoala
 */
class ThemeSiteConfigExtension extends DataExtension
{
    const BACKGROUND_NO_REPEAT  = 'no-repeat';
    const BACKGROUND_REPEAT     = 'repeat';
    const BACKGROUND_REPEAT_X   = 'repeat-x';
    const BACKGROUND_REPEAT_Y   = 'repeat-y';
    const BACKGROUND_SIZE_COVER = 'cover';
    const BACKGROUND_SIZE_FULL  = '100%';

    private static $db               = array(
        'BaseColor' => 'DBColor',
        'PrimaryColor' => 'DBColor',
        'SecondaryColor' => 'DBColor',
        'GoogleAnalyticsCode' => 'Varchar',
        'HeaderFont' => 'Varchar(100)',
        'BodyFont' => 'Varchar(100)',
        'GoogleFonts' => 'Varchar(255)',
        'BackgroundRepeat' => "Enum('no-repeat,repeat,repeat-x,repeat-y','no-repeat')",
    );
    private static $has_one          = array(
        'Logo' => 'Image',
        'Icon' => 'Image', // Will be converted to favicon
    );
    private static $many_many        = array(
        'BackgroundImages' => 'Image'
    );
    private static $defaults         = array(
        'BaseColor' => '#ffffff',
        'PrimaryColor' => '#284d6d',
        'SecondaryColor' => '#44c8f4',
        'HeaderFont' => "'Open Sans', Arial, Helvetica, sans-serif",
        'BodyFont' => "'Open Sans', Arial, Helvetica, sans-serif",
        'GoogleFonts' => "family=Open+Sans:400italic,400,600&subset=latin,latin-ext"
    );
    private static $styles_variables = array(
        'BaseColor', 'PrimaryColor', 'SecondaryColor', 'HeaderFont', 'BodyFont'
    );

    /**
     * @var Image
     */
    protected static $background_image        = null;
    protected static $background_image_repeat = null;
    protected static $background_size         = 'cover';

    public static function getBackgroundImage()
    {
        return self::$background_image;
    }

    public static function setBackgroundImage(Image $background_image,
                                              $repeat = null, $size = null)
    {
        self::$background_image = $background_image;
        if ($repeat !== null) {
            self::$background_image_repeat = $repeat;
        }
        if ($size !== null) {
            self::$background_size = $size;
        }
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Theme');
        $themeDropdownField = new DropdownField("Theme",
            _t('SiteConfig.THEME', 'Theme'), $this->getAvailableThemesExtended());
        $themeDropdownField->setEmptyString(_t('SiteConfig.DEFAULTTHEME',
                '(Use default theme)'));
        $fields->addFieldToTab('Root.Theme', $themeDropdownField);

        // Colors
        $fields->addFieldToTab('Root.Theme',
            new HeaderField('ColorH',
            _t('ThemeSiteConfigExtension.ColorH', 'Colors')));
        $fields->addFieldToTab('Root.Theme',
            $BaseColor = new MiniColorsField('BaseColor',
            _t('ThemeSiteConfigExtension.BaseColor', 'Base Color')));
        $BaseColor->setDescription(_t('ThemeSiteConfigExtension.BaseColorDesc',
                "The background color of your website"));
        $fields->addFieldToTab('Root.Theme',
            new MiniColorsField('PrimaryColor',
            _t('ThemeSiteConfigExtension.PrimaryColor', 'Primary Color')));
        $fields->addFieldToTab('Root.Theme',
            new MiniColorsField('SecondaryColor',
            _t('ThemeSiteConfigExtension.SecondaryColor', 'Secondary Color')));

        // Fonts
        $fields->addFieldToTab('Root.Theme',
            new HeaderField('FontsH',
            _t('ThemeSiteConfigExtension.FontsH', 'Fonts')));
        $fields->addFieldToTab('Root.Theme',
            $hf = new TextField('HeaderFont',
            _t('ThemeSiteConfigExtension.HeaderFont', 'Header Font')));
        $fields->addFieldToTab('Root.Theme',
            $bf = new TextField('BodyFont',
            _t('ThemeSiteConfigExtension.BodyFont', 'Body Font')));
        $fields->addFieldToTab('Root.Theme',
            $gf = new TextField('GoogleFonts',
            _t('ThemeSiteConfigExtension.GoogleFonts', 'Google Fonts')));


        $hf->setAttribute('placeholder', 'Arial, Helvetica, sans-serif');
        $bf->setAttribute('placeholder', 'Arial, Helvetica, sans-serif');
        $gf->setAttribute('placeholder',
            'family=Open+Sans:400italic,400,600&subset=latin,latin-ext');

        // Images
        $fields->addFieldToTab('Root.Theme',
            new HeaderField('ImagesH',
            _t('ThemeSiteConfigExtension.ImagesH', 'Images')));
        $fields->addFieldToTab('Root.Theme',
            ImageUploadField::createForClass($this, 'Logo',
                _t('ThemeSiteConfigExtension.Logo', 'Logo')));
        $fields->addFieldToTab('Root.Theme',
            $icon = ImageUploadField::createForClass($this, 'Icon',
                _t('ThemeSiteConfigExtension.Icon', 'Icon')));

        if (is_file(Director::baseFolder().$this->FaviconPath())) {
            $icon->setDescription(_t('ThemeSiteConfigExtension.FaviconPreview',
                    'Favicon preview').' <img src="'.$this->FaviconPath().'" alt="Favicon" />');
        } else {
            $icon->setDescription(_t('ThemeSiteConfigExtension.NoFavicon',
                    'No favicon created for this site'));
        }

        $fields->addFieldToTab('Root.Theme',
            ImageUploadField::createForClass($this, 'BackgroundImages',
                _t('ThemeSiteConfigExtension.BackgroundImages',
                    'Background Images')));

        $fields->addFieldToTab('Root.Theme',
            new DropdownField('BackgroundRepeat',
            _t('ThemeSiteConfigExtension.BackgroundRepeat', 'Background Repeat'),
            array(
            self::BACKGROUND_NO_REPEAT => 'no repeat',
            self::BACKGROUND_REPEAT => 'repeat',
            self::BACKGROUND_REPEAT_X => 'repeat x',
            self::BACKGROUND_REPEAT_Y => 'repeat y',
        )));

        if (Director::isDev() || Permission::check('ADMIN')) {
            $fields->addFieldToTab('Root.Theme',
                new HeaderField('ThemeDevHeader', 'Dev tools'));
            $fields->addFieldToTab('Root.Theme',
                new CheckboxField('RefreshTheme'));
            $fields->addFieldToTab('Root.Theme',
                new CheckboxField('RefreshIcon'));
        }

        // Simple Google Analytics helper, disable if other extension are found
        if (!$this->owner->hasExtension('GoogleConfig') && !$this->owner->hasExtension('ZenGoogleAnalytics')) {
            $fields->addFieldToTab('Root.Main',
                $ga = new TextField('GoogleAnalyticsCode'));
            $ga->setAttribute('placeholder', 'UA-0000000-00');
        }

        return $fields;
    }

    /**
     * Check if GoogleAnalytics is enabled
     * @return boolean
     */
    public function GoogleAnalyticsEnabled()
    {
        if (Director::isDev()) {
            return false;
        }
        if ($this->owner->GoogleAnalyticsCode) {
            return true;
        }
        return false;
    }

    /**
     * Get all available themes that haven't been marked as disabled.
     * @param string $baseDir Optional alternative theme base directory for testing
     * @return array of theme directory names
     */
    public function getAvailableThemesExtended($baseDir = null)
    {
        if (class_exists('Subsite') && Subsite::currentSubsiteID()) {
            $subsiteThemes = Subsite::config()->allowed_themes;
            // Make sure set theme is allowed
            $subsite       = Subsite::currentSubsite();
            if ($subsite->Theme && !in_array($subsite->Theme, $subsiteThemes)) {
                $subsiteThemes[] = $subsite->Theme;
            }
            // Make sure default theme is allowed
            $theme = Config::inst()->get('SSViewer', 'theme');
            if ($theme && !in_array($theme, $subsiteThemes)) {
                $subsiteThemes[] = $theme;
            }
            return array_combine($subsiteThemes, $subsiteThemes);
        }
        $themes   = SSViewer::get_themes($baseDir);
        $disabled = (array) $this->owner->config()->disabled_themes;
        foreach ($disabled as $theme) {
            if (isset($themes[$theme])) unset($themes[$theme]);
        }
        return $themes;
    }

    /**
     * @return Image
     */
    public function RandomBackgroundImage()
    {
        /* @var $img Image */
        $img = $this->owner->BackgroundImages()->sort('RAND()')->first();
        return $img;
    }

    /**
     * A style ready to be included in the styles attribute
     * @return string
     */
    public function BackgroundImageStyles()
    {
        if (self::$background_image) {
            $img                           = self::$background_image;
            $this->owner->BackgroundRepeat = self::$background_image_repeat ? self::$background_image_repeat
                    : self::BACKGROUND_NO_REPEAT;
        } else {
            $img = $this->RandomBackgroundImage();
        }
        if ($img) {
            $backgroundSize = 'background-size:'.self::$background_size.';background-repeat:no-repeat';
            $resizedImage   = $img;
            // If we use a pattern, repeat it accordingly
            if ($this->owner->BackgroundRepeat !== self::BACKGROUND_NO_REPEAT) {
                $backgroundSize = 'background-size:initial;background-repeat:'.$this->owner->BackgroundRepeat;
            }
            // Or resize to a nice size and stretch
            else {
                $resizedImage = $img->SetWidth(1800);
                if (!$resizedImage) {
                    $resizedImage = $img;
                }
            }
            return "background-image:url('".$resizedImage->Link()."');$backgroundSize";
        }
    }

    /**
     * Get a path to a favico stored in assets folder
     * @param int $subsiteID
     * @return string
     */
    public function FaviconPath($subsiteID = null)
    {
        if (class_exists('Subsite')) {
            if ($subsiteID === null) {
                $subsiteID = Subsite::currentSubsiteID();
            }
            $path = '/assets/Theme/favico-'.$subsiteID.'.ico';
        } else {
            $path = '/assets/Theme/favico.ico';
        }
        return $path;
    }

    /**
     * Get a path to styles
     * @param int $subsiteID
     * @return string
     */
    public function StylesPath($subsiteID = null)
    {
        if (class_exists('Subsite')) {
            if ($subsiteID === null) {
                $subsiteID = Subsite::currentSubsiteID();
            }
            $path = '/assets/Theme/styles-'.$subsiteID.'.css';
        } else {
            $path = '/assets/Theme/styles.css';
        }
        return $path;
    }

    /**
     * Compile styles from theme/css/all.less to assets/Theme/styles.css
     */
    public function compileStyles()
    {
        $destination = Director::baseFolder().$this->StylesPath();

        if ($this->owner->Theme) {
            $themeDir = 'themes/'.$this->owner->Theme;
        } else {
            $themeDir = SSViewer::get_theme_folder();
        }
        $options = array();
        if (Director::isLive()) {
            $options['compress'] = true;
        }
        if (Director::isDev()) {
            $options['sourceMap'] = true;
        }
        $options['cache_dir'] = TEMP_FOLDER;
        $parser               = new Less_Parser($options);
        try {
            $parser->parseFile(Director::baseFolder().'/'.$themeDir.'/css/all.less',
                '/'.$themeDir.'/css');
            $vars = array(
                'base_color' => '#ffffff'
            );
            foreach (self::$styles_variables as $var) {
                if ($this->owner->$var) {
                    $less_var        = strtolower(preg_replace('/([a-z])([A-Z])/',
                            '$1-$2', $var));
                    $vars[$less_var] = $this->owner->$var;
                }
            }

            if (!empty($vars)) {
                $parser->ModifyVars($vars);
            }
            $css = $parser->getCss();

            $baseDir = Director::baseFolder().'/assets/Theme';
            if (!is_dir($baseDir)) {
                mkdir($baseDir, 0777, true);
            }

            file_put_contents($destination, $css);
        } catch (Exception $ex) {
            SS_Log::log('Failed to create css files : '.$ex->getMessage(),
                SS_Log::DEBUG);
        }
    }

    public function HeadScripts()
    {
        return ThemeHeadRequirements::output();
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        $baseDir = Director::baseFolder().'/assets/Theme';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        $changes       = array_keys($this->owner->getChangedFields(false, 1));
        $shouldCompile = false;
        foreach ($changes as $change) {
            if (in_array($change, self::$styles_variables)) {
                $shouldCompile = true;
            }
        }

        // Create theme according to colors
        if (!empty($_POST['RefreshTheme']) || $shouldCompile) {
            $this->compileStyles();
        }

        // Create favicon
        $destination = Director::baseFolder().$this->FaviconPath();
        if ($this->owner->IconID && (!empty($_POST['RefreshIcon']) || ($this->owner->isChanged('IconID')))) {
            $source  = $this->owner->Icon()->getFullPath();
            $ico_lib = new PHP_ICO($source, array(array(16, 16)));
            try {
                $ico_lib->save_ico($destination);
            } catch (Exception $ex) {
                SS_Log::log('Failed to create favicon : '.$ex->getMessage(),
                    SS_Log::DEBUG);
            }
        }
    }
}