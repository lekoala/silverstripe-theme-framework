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

        $themeDropdownField = $fields->dataFieldByName('Theme');
        if ($themeDropdownField) {
            $fields->insertBefore($themeDropdownField, 'PrimaryColor');
        }

        return $fields;
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
            return "background-image:url('".$img->SetWidth(1200)->Link()."')";
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
            $path = '/assets/favico/favico-'.$subsiteID.'.ico';
        } else {
            $path = '/assets/favico/favico.ico';
        }
        return $path;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        // Create favicon
        if ($this->owner->IconID) {
            $source      = $this->owner->Icon()->getFullPath();
            $destination = Director::baseFolder().$this->FaviconPath();
            $dir         = dirname($destination);

            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

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