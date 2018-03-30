<?php

/**
 * Rebuild styles for all subsites
 *
 * @author Koala
 */
class ThemeRebuildAllStylesTask extends BuildTask
{

    public function run($request)
    {
        set_time_limit(0);
        increase_memory_limit_to();
        
        if(class_exists('Subsite')) Subsite::$disable_subsite_filter = true;

        $mainConfig = SiteConfig::current_site_config();
        $mainConfig->compileStyles();
        DB::alteration_message("Compile styles for main site");

        if(class_exists('Subsite')) {
            $subsites = Subsite::get();
            foreach ($subsites as $subsite) {
                $subsiteConfig = SiteConfig::get()->filter('SubsiteID', $subsite->ID)->first();
                if (!$subsiteConfig) {
                    DB::alteration_message("No config for subsite ".$subsite->ID,
                        "error");
                    continue;
                }
                $subsiteConfig->compileStyles();
                DB::alteration_message("Compile styles for subsite ".$subsite->ID);
            }
        }
        DB::alteration_message("All done");
    }
}
