<?php

/**
 * Description of ThemeSitetreeExtension
 *
 * @author Koala
 */
class ThemeSiteTreeExtension extends DataExtension
{

    /**
     * Return "link", "current" or section depending on if this page is the current page, or not on the current page but
     * in the current section.
     *
     * @return string
     */
    public function UKLinkingMode()
    {
        if ($this->owner->isCurrent()) {
            return 'uk-active';
        } elseif ($this->owner->isSection()) {
            return 'uk-parent';
        } else {
            return '';
        }
    }
}