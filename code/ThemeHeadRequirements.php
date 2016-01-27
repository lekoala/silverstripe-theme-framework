<?php

/**
 * Requirements that go into the head
 *
 * You must set Requirements::set_force_js_to_bottom(true); for this to work properly
 *
 * @author Koala
 */
class ThemeHeadRequirements
{
    protected static $javascript   = array();
    protected static $customScript = array();

    /**
     * Finds the path for specified file.
     *
     * @param string $fileOrUrl
     * @return string|boolean
     */
    protected static function path_for_file($fileOrUrl)
    {
        if (preg_match('{^//|http[s]?}', $fileOrUrl)) {
            return $fileOrUrl;
        } elseif (Director::fileExists($fileOrUrl)) {
            $filePath    = preg_replace('/\?.*/', '',
                Director::baseFolder().'/'.$fileOrUrl);
            $prefix      = Director::baseURL();
            $mtimesuffix = "";
            $suffix      = '';
            if (Requirements::get_suffix_requirements()) {
                $mtimesuffix = "?m=".filemtime($filePath);
                $suffix      = '&';
            }
            if (strpos($fileOrUrl, '?') !== false) {
                if (strlen($suffix) == 0) {
                    $suffix = '?';
                }
                $suffix .= substr($fileOrUrl, strpos($fileOrUrl, '?') + 1);
                $fileOrUrl = substr($fileOrUrl, 0, strpos($fileOrUrl, '?'));
            } else {
                $suffix = '';
            }
            return "{$prefix}{$fileOrUrl}{$mtimesuffix}{$suffix}";
        } else {
            return false;
        }
    }

    public static function output()
    {
        $jsRequirements = '';
        foreach (self::$javascript as $file => $index) {
            $path = Convert::raw2xml(self::path_for_file($file));
            if ($path) {
                $jsRequirements .= "<script src=\"$path\"></script>\n";
            }
        }
        foreach (self::$customScript as $script) {
            $jsRequirements .= "<script>\n//<![CDATA[\n";
            $jsRequirements .= "$script\n";
            $jsRequirements .= "\n//]]>\n</script>\n";
        }
        return $jsRequirements;
    }

    /**
     * Register the given javascript file as required.
     * Filenames should be relative to the base, eg, 'framework/javascript/loader.js'
     */
    public static function javascript($file)
    {
        self::$javascript[$file] = true;
    }

    /**
     * Load the given javascript template with the page.
     * @param file The template file to load.
     * @param vars The array of variables to load.  These variables are loaded via string search & replace.
     */
    public static function javascriptTemplate($file, $vars, $uniquenessID = null)
    {
        $script  = file_get_contents(Director::getAbsFile($file));
        $search  = array();
        $replace = array();

        if ($vars) {
            foreach ($vars as $k => $v) {
                $search[]  = '$'.$k;
                $replace[] = str_replace("\\'", "'", Convert::raw2js($v));
            }
        }

        $script = str_replace($search, $replace, $script);
        self::customScript($script, $uniquenessID);
    }

    /**
     * Add the javascript code to the header of the page
     * @param script The script content
     * @param uniquenessID Use this to ensure that pieces of code only get added once.
     */
    public static function customScript($script, $uniquenessID = null)
    {
        if ($uniquenessID) {
            self::$customScript[$uniquenessID] = $script;
        } else {
            self::$customScript[]              = $script;
        }

        $script .= "\n";
    }
}
