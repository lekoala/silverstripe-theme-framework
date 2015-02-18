<?php

/**
 * ThemeSiteConfigExtension
 *
 * @author lekoala
 */
class ThemeSiteConfigExtension extends DataExtension
{
    private static $db        = array(
        'PrimaryColor' => 'DBColor',
        'SecondaryColor' => 'DBColor',
    );
    private static $has_one   = array(
        'Logo' => 'Image',
        'Icon' => 'Image', // Will be converted to favicon
    );
    private static $many_many = array(
        'BackgroundImages' => 'Image'
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName('Theme');
        $themeDropdownField = new DropdownField("Theme",
            _t('SiteConfig.THEME', 'Theme'), $this->getAvailableThemesExtended());
        $themeDropdownField->setEmptyString(_t('SiteConfig.DEFAULTTHEME',
                '(Use default theme)'));
        $fields->addFieldToTab('Root.Theme', $themeDropdownField);

        $fields->addFieldToTab('Root.Theme', new MiniColorsField('PrimaryColor'));
        $fields->addFieldToTab('Root.Theme',
            new MiniColorsField('SecondaryColor'));
        $fields->addFieldToTab('Root.Theme',
            ImageUploadField::createForClass($this, 'Logo'));
        $fields->addFieldToTab('Root.Theme',
            $icon = ImageUploadField::createForClass($this, 'Icon'));

        if (is_file(Director::baseFolder().$this->FaviconPath())) {
            $icon->setDescription(_t('ThemeSiteConfigExtension.Favicon',
                    'Favicon preview').' <img src="'.$this->FaviconPath().'" alt="Favicon" />');
        } else {
            $icon->setDescription(_t('ThemeSiteConfigExtension.NoFavicon',
                    'No favicon created for this site'));
        }

        $fields->addFieldToTab('Root.Theme',
            ImageUploadField::createForClass($this, 'BackgroundImages'));

        if (Director::isDev()) {
            $fields->addFieldToTab('Root.Theme',
                new HeaderField('ThemeDevHeader', 'Dev tools'));
            $fields->addFieldToTab('Root.Theme',
                new CheckboxField('RefreshTheme'));
            $fields->addFieldToTab('Root.Theme',
                new CheckboxField('RefreshIcon'));
        }

        return $fields;
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
        $img = $this->RandomBackgroundImage();
        if ($img) {
            return "background-image:url('".$img->SetWidth(1800)->Link()."')";
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

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        $baseDir = Director::baseFolder().'/assets/Theme';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }

        // Create theme according to colors
        $destination = Director::baseFolder().$this->StylesPath();
        if (!empty($_POST['RefreshTheme']) || $this->owner->isChanged('PrimaryColor')
            || $this->owner->isChanged('SecondaryColor')) {
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
                $parser->parseFile(Director::baseFolder().'/'.$themeDir.'/css/all.less');
                $vars = array();
                if ($this->owner->PrimaryColor) {
                    $vars['primary-color'] = $this->owner->PrimaryColor;
                }
                if ($this->owner->SecondaryColor) {
                    $vars['secondary-color'] = $this->owner->SecondaryColor;
                }
                if (!empty($vars)) {
                    $parser->ModifyVars($vars);
                }
                $css = $parser->getCss();
                file_put_contents($destination, $css);
            } catch (Exception $ex) {
                SS_Log::log('Failed to create css files : '.$ex->getMessage(),
                    SS_Log::DEBUG);
            }
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